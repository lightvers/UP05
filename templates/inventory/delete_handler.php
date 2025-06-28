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
    'redirect' => null
];

try {
    require_login();
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Неверный метод запроса');
    }

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        throw new Exception('Неверный ID инвентаризации');
    }

    $db = (new Database())->connect();
    $inventory = new Inventory($db);

    if (!$inventory->getById($id)) {
        throw new Exception('Инвентаризация не найдена');
    }

    if ($inventory->delete()) {
        $response['success'] = true;
        $response['message'] = 'Инвентаризация успешно удалена';
        $response['redirect'] = '/UP/templates/inventory/index.php';
    } else {
        throw new Exception('Ошибка при удалении инвентаризации');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;