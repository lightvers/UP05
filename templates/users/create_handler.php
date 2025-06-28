<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';

// Authentication and authorization check
require_login();
require_admin(); // Only admins can create users

// Database connection
$db = (new Database())->connect();
$user = new User($db);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare user data
    $user->username = trim($_POST['username'] ?? '');
    $user->password = trim($_POST['password'] ?? '');
    $user->role = trim($_POST['role'] ?? '');
    $user->email = trim($_POST['email'] ?? '');
    $user->last_name = trim($_POST['last_name'] ?? '');
    $user->first_name = trim($_POST['first_name'] ?? '');
    $user->middle_name = trim($_POST['middle_name'] ?? '');
    $user->phone = trim($_POST['phone'] ?? '');
    $user->address = trim($_POST['address'] ?? '');

    // Validation
    $errors = [];
    if (empty($user->username)) $errors['username'] = 'Логин обязателен';
    if (empty($user->password)) $errors['password'] = 'Пароль обязателен';
    if (empty($user->last_name)) $errors['last_name'] = 'Фамилия обязательна';
    if (empty($user->role)) $errors['role'] = 'Роль обязательна';

    if (empty($errors)) {
        try {
            if ($user->create()) {
                // Устанавливаем сообщение об успехе в сессию
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => 'Пользователь успешно добавлен'
                ];

                // Перенаправляем на страницу со списком пользователей
                header('Location: index.php');
                exit();
            } else {
                $errors['username'] = 'Ошибка при добавлении пользователя. Возможно, логин уже занят.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'Ошибка сервера: ' . $e->getMessage();
        }
    }

    // Если есть ошибки, заполняем сессию для показа ошибок и передаем данные формы обратно
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Пожалуйста, исправьте ошибки в форме'
        ];

        // Перенаправляем обратно на форму
        header('Location: create.php');
        exit();
    }
} else {
    // Если это не POST запрос, просто перенаправляем на страницу создания
    header('Location: create.php');
    exit();
}
