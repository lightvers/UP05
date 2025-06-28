<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Equipment.php';
require_once '../../models/Classroom.php';

// Проверка авторизации и прав
require_login();

// Устанавливаем заголовок JSON
header('Content-Type: application/json');

// Подготовка ответа
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // Проверка метода запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Метод не поддерживается");
    }

    // Проверка CSRF-токена (рекомендуется)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception("Неверный CSRF-токен");
    }

    // Получаем ID пользователя
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        throw new Exception("Неверный ID пользователя");
    }

    // Проверяем, не пытается ли пользователь удалить себя
    if ($id == $_SESSION['user_id']) {
        throw new Exception("Вы не можете удалить свой аккаунт");
    }

    // Инициализация подключения к БД
    $db = (new Database())->connect();
    $user = new User($db);
    $equipment = new Equipment($db);
    $classroom = new Classroom($db);

    // Проверяем существование пользователя
    if (!$user->getById($id)) {
        throw new Exception("Пользователь не найден");
    }

    // Проверяем зависимости
    $hasEquipment = $equipment->countByResponsibleUser($id) > 0;
    $hasClassrooms = $classroom->countByResponsibleUser($id) > 0;

    // Начинаем транзакцию
    $db->beginTransaction();

    try {
        // 1. Очищаем зависимости в оборудовании
        if ($hasEquipment && !$equipment->clearResponsibleUser($id)) {
            throw new Exception("Ошибка при очистке оборудования");
        }

        // 2. Очищаем зависимости в аудиториях
        if ($hasClassrooms && !$classroom->clearResponsibleUser($id)) {
            throw new Exception("Ошибка при очистке аудиторий");
        }

        // 3. Удаляем пользователя
        $user->id = $id;
        if (!$user->delete()) {
            throw new Exception("Ошибка при удалении пользователя");
        }

        // Если все успешно - коммитим
        $db->commit();

        $response['success'] = true;
        $response['message'] = "Пользователь успешно удален";
        
        // Сообщение для отображения после редиректа
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => $response['message']
        ];
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Ошибка при удалении пользователя: " . $e->getMessage());
}

echo json_encode($response);
exit();