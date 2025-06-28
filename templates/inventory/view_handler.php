<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/User.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'inventory' => null,
    'user_role' => null
];

try {
    require_login();
    $response['user_role'] = get_current_user_role();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        throw new Exception('Неверный ID инвентаризации');
    }

    $db = (new Database())->connect();
    $inventory = new Inventory($db);
    $user = new User($db);

    if (!$inventory->getById($id)) {
        throw new Exception('Инвентаризация не найдена');
    }

    $creator = $user->getById($inventory->created_by_user_id);

    $response['success'] = true;
    $response['inventory'] = [
        'id' => $inventory->id,
        'name' => $inventory->name,
        'start_date' => $inventory->start_date,
        'end_date' => $inventory->end_date,
        'status' => $inventory->status,
        'created_by' => $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'Неизвестно'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;