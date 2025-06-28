<?php
require_once '../../config/database.php';
require_once '../../models/ReferenceItem.php';

header('Content-Type: application/json');

$db = (new Database())->connect();
$reference = new ReferenceItem($db);

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    $type = $_POST['type'] ?? $_GET['type'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Название обязательно для заполнения');
            }
            
            if ($reference->nameExists($type, $name)) {
                throw new Exception('Запись с таким названием уже существует');
            }
            
            if ($reference->create($type, $name, $description)) {
                $response['success'] = true;
                $response['message'] = 'Запись успешно создана';
            } else {
                throw new Exception('Ошибка при создании записи');
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Название обязательно для заполнения');
            }
            
            if ($reference->nameExists($type, $name, $id)) {
                throw new Exception('Запись с таким названием уже существует');
            }
            
            if ($reference->update($id, $type, $name, $description)) {
                $response['success'] = true;
                $response['message'] = 'Запись успешно обновлена';
            } else {
                throw new Exception('Ошибка при обновлении записи');
            }
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            
            if ($reference->delete($id, $type)) {
                $response['success'] = true;
                $response['message'] = 'Запись успешно удалена';
            } else {
                throw new Exception('Ошибка при удалении записи');
            }
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>