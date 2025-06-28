<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';

// Authentication and authorization check
require_login();
require_admin();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'errors' => [],
    'user' => null
];

try {
    $db = (new Database())->connect();
    $user_model = new User($db);

    // Get user ID from URL
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Get user data
    $current_user = $user_model->getById($id);
    if (!$current_user) {
        $response['message'] = 'Пользователь не найден';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    $response['user'] = $current_user;

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? '')
        ];

        // Validation
        $errors = [];
        if (empty($data['username'])) $errors['username'] = 'Логин обязателен';
        if (empty($data['last_name'])) $errors['last_name'] = 'Фамилия обязательна';
        if (empty($data['first_name'])) $errors['first_name'] = 'Имя обязательно';

        if (empty($errors)) {
            // Update user
            $user_model->id = $id;
            $user_model->username = $data['username'];
            $user_model->role = $data['role'];
            $user_model->email = $data['email'];
            $user_model->last_name = $data['last_name'];
            $user_model->first_name = $data['first_name'];
            $user_model->middle_name = $data['middle_name'];
            $user_model->phone = $data['phone'];
            $user_model->address = $data['address'];

            if ($user_model->update()) {
                $response['success'] = true;
                $response['message'] = 'Данные пользователя успешно обновлены';
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => $response['message']
                ];
            } else {
                $errors['general'] = 'Ошибка при обновлении пользователя';
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
