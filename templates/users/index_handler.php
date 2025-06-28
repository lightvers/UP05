<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';

// Authentication and authorization check
require_login();
require_admin(); // Uncomment if only admins should access this

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'users' => [],
    'pagination' => [
        'current_page' => 1,
        'per_page' => 10,
        'total_users' => 0,
        'total_pages' => 1
    ],
    'search' => '',
    'user_role' => null
];

try {
    $db = (new Database())->connect();
    $user = new User($db);

    // Get pagination and search parameters
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $search = $_GET['search'] ?? '';

    // Validate page number
    if ($current_page < 1) {
        $current_page = 1;
    }

    // Get paginated users
    $total_users = $user->countAll($search);
    $total_pages = max(1, ceil($total_users / $per_page));

    // Validate page range
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }

    // Защита от ошибки при получении данных
    try {
        $stmt = $user->getPaginated($current_page, $per_page, $search);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка при получении пользователей: " . $e->getMessage());
        $users = [];
    }

    // Prepare response
    $response['success'] = true;
    $response['users'] = $users;
    $response['pagination'] = [
        'current_page' => $current_page,
        'per_page' => $per_page,
        'total_users' => $total_users,
        'total_pages' => $total_pages
    ];
    $response['search'] = $search;
    $response['user_role'] = get_current_user_role();

} catch (Exception $e) {
    $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
