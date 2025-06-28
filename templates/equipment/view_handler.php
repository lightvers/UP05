<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../models/Equipment.php';

$response = [
    'success' => false,
    'data' => null,
    'message' => ''
];

try {
    // Подключаемся к базе данных
    $db = (new Database())->connect();
    if (!$db) {
        throw new Exception('Не удалось подключиться к базе данных');
    }

    $equipment = new Equipment($db);

    // Получаем ID оборудования
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        throw new Exception('Не указан ID оборудования');
    }

    // Получаем основные данные оборудования
    $equipmentData = $equipment->getById($id);
    if (!$equipmentData) {
        throw new Exception('Оборудование не найдено');
    }

    // Инициализируем дополнительные данные
    $history = [];
    $network = null;
    $consumables = [];

    try {
        // Запрос истории перемещений
        $historyQuery = "SELECT
                            h.from_id, h.to_id, h.changed_at, h.comments,
                            fc.name as from_classroom_name,
                            tc.name as to_classroom_name,
                            u.last_name as user_last_name,
                            u.first_name as user_first_name
                        FROM change_history h
                        LEFT JOIN classrooms fc ON h.from_id = fc.id
                        LEFT JOIN classrooms tc ON h.to_id = tc.id
                        LEFT JOIN users u ON h.changed_by_user_id = u.id
                        WHERE h.entity_type = 'equipment_movement' AND h.equipment_id = ?
                        ORDER BY h.changed_at DESC";
        $historyStmt = $db->prepare($historyQuery);
        $historyStmt->execute([$id]);
        $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

        // Запрос сетевых настроек
        $networkQuery = "SELECT * FROM network_settings WHERE equipment_id = ?";
        $networkStmt = $db->prepare($networkQuery);
        $networkStmt->execute([$id]);
        $network = $networkStmt->fetch(PDO::FETCH_ASSOC);

        // Запрос расходных материалов (исправленный)
        $consumablesQuery = "SELECT 
                        c.id, 
                        c.name,
                        t.name as type_name
                    FROM consumables c
                    LEFT JOIN reference_items t ON c.type_id = t.id AND t.type = 'consumable_type'
                    WHERE c.equipment_id = ?
                    ORDER BY c.name";
        $consumablesStmt = $db->prepare($consumablesQuery);
        $consumablesStmt->execute([$id]);
        $consumables = $consumablesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Логирование для отладки
        error_log("Equipment ID: $id, Consumables found: " . count($consumables));

    } catch (PDOException $e) {
        error_log('Ошибка при получении дополнительных данных: ' . $e->getMessage());
    }

    // Формируем успешный ответ
    $response['success'] = true;
    $response['data'] = [
        'equipment' => $equipmentData,
        'history' => $history,
        'network' => $network,
        'consumables' => $consumables
    ];

} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных: ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(404);
}

// Возвращаем JSON-ответ
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;