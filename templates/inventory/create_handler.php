<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    require_login();
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неверный метод запроса');
    }

    $errors = [];
    $name = trim($_POST['name'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if (empty($name)) $errors['name'] = 'Введите название';
    if (empty($start_date)) $errors['start_date'] = 'Укажите дату начала';
    if (empty($end_date)) $errors['end_date'] = 'Укажите дату окончания';

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new Exception('Исправьте ошибки в форме');
    }

    $db = (new Database())->connect();
    $inventory = new Inventory($db);

    $inventory->name = $name;
    $inventory->start_date = $start_date;
    $inventory->end_date = $end_date;
    $inventory->created_by_user_id = $_SESSION['user_id'];
    $inventory->status = 'planned';

    if ($inventory->create()) {
        $response['success'] = true;
        $response['message'] = 'Инвентаризация успешно создана';
    } else {
        throw new Exception('Ошибка при создании инвентаризации');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;