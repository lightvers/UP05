<?php
require_once '../../includes/auth.php';

// Проверяем права администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Доступ запрещен',
        'errors' => []
    ]);
    exit;
}

header('Content-Type: application/json');

require_once '../../models/Classroom.php';
require_once '../../models/User.php';

$db = (new Database())->connect();
$classroom = new Classroom($db);

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из запроса
    $name = trim($_POST['name'] ?? '');
    $short_name = trim($_POST['short_name'] ?? '');
    $responsible_user_id = !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : null;
    $temp_responsible_user_id = !empty($_POST['temp_responsible_user_id']) ? (int)$_POST['temp_responsible_user_id'] : null;

    // Валидация
    if (empty($name)) {
        $response['errors']['name'] = 'Название аудитории обязательно';
    }

    if (!empty($response['errors'])) {
        $response['message'] = 'Исправьте ошибки в форме';
        echo json_encode($response);
        exit;
    }

    // Создание аудитории
    try {
        $classroom->name = $name;
        $classroom->short_name = $short_name;
        $classroom->responsible_user_id = $responsible_user_id;
        $classroom->temp_responsible_user_id = $temp_responsible_user_id;

        if ($classroom->create()) {
            $response['success'] = true;
            $response['message'] = 'Аудитория успешно добавлена';
            $response['redirect'] = 'index.php';
        } else {
            $response['message'] = 'Ошибка при добавлении аудитории';
        }
    } catch (Exception $e) {
        $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// Если запрос не POST, редиректим на страницу создания
header('Location: create.php');
exit;
