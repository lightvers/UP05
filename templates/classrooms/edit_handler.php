<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Проверяем права администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Доступ запрещен';
    header('Location: /UP/templates/classrooms/index.php');
    exit;
}

require_once '../../config/database.php';
require_once '../../models/Classroom.php';
require_once '../../models/User.php';

// Инициализация подключения к БД
$database = new Database();
$db = $database->connect();
$classroom = new Classroom($db);

// Получаем данные из запроса
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$short_name = trim($_POST['short_name'] ?? '');
$responsible_user_id = !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : null;
$temp_responsible_user_id = !empty($_POST['temp_responsible_user_id']) ? (int)$_POST['temp_responsible_user_id'] : null;

// Валидация
if (empty($name)) {
    $_SESSION['error'] = 'Название аудитории обязательно';
    header("Location: edit.php?id=$id");
    exit;
}

// Обновление данных
try {
    $classroom->id = $id;
    $classroom->name = $name;
    $classroom->short_name = $short_name;
    $classroom->responsible_user_id = $responsible_user_id;
    $classroom->temp_responsible_user_id = $temp_responsible_user_id;

    if ($classroom->update()) {
        $_SESSION['success'] = 'Аудитория успешно обновлена';
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении аудитории';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Ошибка сервера: ' . $e->getMessage();
}

header('Location: index.php');
exit;