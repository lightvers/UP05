<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Inventory.php';

// Authentication and authorization check
require_login();
require_admin(); // Только администраторы могут редактировать инвентаризации

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => [],
    'inventory' => null
];

try {
    $db = (new Database())->connect();
    $inventory_model = new Inventory($db);

    // Get inventory ID from URL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Get inventory data
    if (!$inventory_model->getById($id)) {
        $response['message'] = 'Инвентаризация не найдена';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Преобразуем данные для ответа
    $response['inventory'] = [
        'id' => $inventory_model->id,
        'name' => $inventory_model->name,
        'start_date' => $inventory_model->start_date,
        'end_date' => $inventory_model->end_date,
        'created_by_user_id' => $inventory_model->created_by_user_id,
        'status' => $inventory_model->status
    ];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'status' => trim($_POST['status'] ?? '')
        ];

        // Validation
        $errors = [];
        if (empty($data['name'])) $errors['name'] = 'Название обязательно';
        if (empty($data['start_date'])) $errors['start_date'] = 'Дата начала обязательна';
        if (empty($data['end_date'])) $errors['end_date'] = 'Дата окончания обязательна';
        if (empty($data['status'])) $errors['status'] = 'Статус обязателен';

        // Проверка корректности дат
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = new DateTime($data['start_date']);
            $end = new DateTime($data['end_date']);

            if ($end < $start) {
                $errors['end_date'] = 'Дата окончания не может быть раньше даты начала';
            }
        }

        if (empty($errors)) {
            // Update inventory
            $inventory_model->id = $id;
            $inventory_model->name = $data['name'];
            $inventory_model->start_date = $data['start_date'];
            $inventory_model->end_date = $data['end_date'];
            $inventory_model->status = $data['status'];
            // Сохраняем текущего пользователя, который создал инвентаризацию
            $inventory_model->created_by_user_id = $response['inventory']['created_by_user_id'];

            if ($inventory_model->update()) {
                $response['success'] = true;
                $response['message'] = 'Данные инвентаризации успешно обновлены';
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => $response['message']
                ];
            } else {
                $errors['general'] = 'Ошибка при обновлении инвентаризации';
            }
        }

        $response['errors'] = $errors;
        $response['form_data'] = $data;
    }
} catch (Exception $e) {
    $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
