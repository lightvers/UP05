<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';

// Authentication check
require_login();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'user' => null,
    'equipment' => [],
    'current_user_id' => null,
    'current_user_role' => null
];

try {
    // Validate ID parameter
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $response['message'] = 'Неверный идентификатор пользователя';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    $id = (int)$_GET['id'];

    // Database connection
    $db = (new Database())->connect();
    $user = new User($db);

    // Get user data
    if (!$user->getById($id)) {
        $response['message'] = 'Пользователь не найден';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Get assigned equipment
    $equipment = $user->getAssignedEquipment()->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response['success'] = true;
    $response['user'] = [
        'id' => $user->id,
        'username' => $user->username,
        'last_name' => $user->last_name,
        'first_name' => $user->first_name,
        'middle_name' => $user->middle_name,
        'role' => $user->role,
        'email' => $user->email,
        'phone' => $user->phone,
        'address' => $user->address
    ];
    $response['equipment'] = $equipment;
    $response['current_user_id'] = $_SESSION['user_id'];
    $response['current_user_role'] = get_current_user_role();

} catch (Exception $e) {
    $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
