<?php
ob_start();

try {
    require_once '../../includes/auth.php';
    require_once '../../includes/header.php';
    require_admin();
    require_once '../../models/PdfActGenerator.php';

    // Проверка типа акта
    $type = $_GET['type'] ?? '';
    if (!in_array($type, ['equipment_temporary', 'consumables', 'equipment'])) {
        throw new Exception("Неверный тип акта");
    }

    // Проверка ID сотрудника
    $employee_id = $_GET['employee_id'] ?? null;
    if (!$employee_id || !is_numeric($employee_id)) {
        throw new Exception("Не указан корректный ID сотрудника");
    }

    // Получаем выбранные элементы
    $items_json = $_GET['items'] ?? '';
    if (empty($items_json)) {
        throw new Exception("Не переданы данные о выбранных элементах");
    }

    $selected_items = json_decode($items_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Ошибка при обработке выбранных элементов");
    }

    if (empty($selected_items)) {
        throw new Exception("Не выбрано ни одного элемента");
    }

    // Получаем ID элементов для запроса к БД
    $item_ids = array_column($selected_items, 'id');
    if (empty($item_ids)) {
        throw new Exception("Не переданы ID выбранных элементов");
    }

    // Подключение к базе данных
    $database = new Database();
    $pdo = $database->connect();
    if (!$pdo) {
        throw new Exception("Не удалось подключиться к базе данных");
    }

    // Получение данных сотрудника
    $stmt = $pdo->prepare("SELECT last_name, first_name, middle_name FROM users WHERE id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        throw new Exception("Сотрудник не найден");
    }
    
    $employee_name = trim($employee['last_name'] . ' ' . $employee['first_name'] . ' ' . ($employee['middle_name'] ?? ''));

    // Получение данных оборудования
    $in_clause = implode(',', array_fill(0, count($item_ids), '?'));
    
    switch($type) {
        case 'equipment_temporary':
        case 'equipment':
            $stmt = $pdo->prepare("SELECT id, name, inventory_number, COALESCE(cost, 0) as cost FROM equipment WHERE id IN ($in_clause)");
            break;
        case 'consumables':
            $stmt = $pdo->prepare("SELECT id, name, quantity, COALESCE(cost, 0) as cost FROM consumables WHERE id IN ($in_clause)");
            break;
    }
    
    $stmt->execute($item_ids);
    $items_data = $stmt->fetchAll();

    if (empty($items_data)) {
        throw new Exception("Не найдено данных по выбранным элементам");
    }

    // Объединяем данные из БД с дополнительными данными из формы
    foreach ($items_data as &$item) {
        foreach ($selected_items as $selected) {
            if ($item['id'] == $selected['id']) {
                // Сохраняем количество из формы (если есть)
                if (isset($selected['quantity'])) {
                    $item['quantity'] = $selected['quantity'];
                }
                // Сохраняем инвентарный номер из формы (если есть)
                if (isset($selected['inventory_number'])) {
                    $item['inventory_number'] = $selected['inventory_number'];
                }
                break;
            }
        }
    }

    // Очистка буфера перед генерацией PDF
    ob_end_clean();

    // Генерация PDF
    $pdf = new PdfActGenerator('АКТ приема-передачи');
    
    $data = [
        'employee_name' => $employee_name,
        'transfer_date' => $_GET['transfer_date'] ?? date('Y-m-d'),
        'comments' => $_GET['comments'] ?? ''
    ];

    switch($type) {
        case 'equipment_temporary':
            $data['equipment'] = $items_data;
            $data['return_date'] = $_GET['return_date'] ?? '';
            $pdf->generateEquipmentTemporaryAct($data);
            break;
        case 'consumables':
            $data['consumables'] = $items_data;
            $pdf->generateConsumablesAct($data);
            break;
        case 'equipment':
            $data['equipment'] = $items_data;
            $pdf->generateEquipmentAct($data);
            break;
    }

} catch (Exception $e) {
    ob_end_clean();
    die("Ошибка: " . $e->getMessage());
}