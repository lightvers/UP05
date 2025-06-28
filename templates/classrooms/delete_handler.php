<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Classroom.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Очистка буфера на случай случайных выводов
while (ob_get_level()) ob_end_clean();

// Установка заголовков
header('Content-Type: application/json; charset=utf-8');

// Подготовка ответа
$response = [
    'success' => false,
    'message' => 'Неизвестная ошибка',
    'redirect' => '/UP/templates/classrooms/index.php'
];

try {
    // Проверка метода запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Только POST-запросы разрешены');
    }

    // Проверка авторизации
    require_admin();

    // Получение и валидация ID
    if (!isset($_POST['id'])) {
        throw new Exception('ID аудитории не указан');
    }

    $id = (int)$_POST['id'];
    if ($id <= 0) {
        throw new Exception('Неверный ID аудитории');
    }

    // Подключение к БД
    $db = (new Database())->connect();
    $classroom = new Classroom($db);
    $equipment = new Equipment($db);

    // Проверка существования аудитории
    if (!$classroom->getById($id)) {
        throw new Exception('Аудитория не найдена');
    }

    // Начало транзакции
    $db->beginTransaction();

    // 1. Перемещение оборудования
    if (!$equipment->moveAllFromClassroom($id, null)) {
        throw new Exception('Не удалось переместить оборудование');
    }

    // 2. Удаление аудитории
    if (!$classroom->delete($id)) {
        throw new Exception('Не удалось удалить аудиторию');
    }

    // Подтверждение транзакции
    $db->commit();

    $response['success'] = true;
    $response['message'] = 'Аудитория успешно удалена';

} catch (Exception $e) {
    // Откат транзакции при ошибке
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    $response['message'] = $e->getMessage();
    error_log('[' . date('Y-m-d H:i:s') . '] Ошибка удаления аудитории: ' . $e->getMessage());
}

// Чистый вывод JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;