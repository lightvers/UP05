<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Equipment.php';
// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Метод запроса не поддерживается";
    header('Location: /UP/templates/equipment/index.php');
    exit;
}

// Проверка ID
if (empty($_POST['id'])) {
    $_SESSION['error'] = "ID оборудования не указан";
    header('Location: /UP/templates/equipment/index.php');
    exit;
}

$id = (int)$_POST['id'];

// Подключение к базе данных
try {
    $db = (new Database())->connect();
    $equipment = new Equipment($db);

    // Проверка существования оборудования
    if (!$equipment->getById($id)) {
        $_SESSION['error'] = "Оборудование с ID {$id} не найдено";
        header('Location: /UP/templates/equipment/index.php');
        exit;
    }

    // Получение и валидация данных
    $required_fields = ['name', 'inventory_number', 'status_id'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Поле " . ucfirst(str_replace('_', ' ', $field)) . " обязательно для заполнения.";
            header('Location: /UP/templates/equipment/edit.php?id=' . $id);
            exit;
        }
    }

    // Проверка уникальности инвентарного номера для другого оборудования
    $current_inventory = $equipment->inventory_number;
    $new_inventory = trim($_POST['inventory_number']);

    if ($current_inventory != $new_inventory && $equipment->inventoryNumberExists($new_inventory)) {
        $_SESSION['error'] = "Оборудование с таким инвентарным номером уже существует";
        header('Location: /UP/templates/equipment/edit.php?id=' . $id);
        exit;
    }

    // Подготовка данных
    $equipment->id = $id;
    $equipment->name = trim($_POST['name']);
    $equipment->inventory_number = $new_inventory;
    $equipment->cost = !empty($_POST['cost']) ? trim($_POST['cost']) : null;
    $equipment->current_classroom_id = !empty($_POST['classroom_id']) ? (int)$_POST['classroom_id'] : null;
    $equipment->responsible_user_id = !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : null;
    $equipment->temp_responsible_user_id = !empty($_POST['temp_responsible_user_id']) ? (int)$_POST['temp_responsible_user_id'] : null;
    $equipment->status_id = (int)$_POST['status_id'];
    $equipment->comments = !empty($_POST['comments']) ? trim($_POST['comments']) : null;

    // Обновление оборудования
    if ($equipment->update()) {
        $_SESSION['success'] = "Оборудование успешно обновлено!";
        header('Location: /UP/templates/equipment/index.php');
        exit;
    } else {
        throw new Exception("Ошибка при обновлении оборудования в базе данных");
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
    header('Location: /UP/templates/equipment/edit.php?id=' . $id);
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: /UP/templates/equipment/edit.php?id=' . $id);
    exit;
}
