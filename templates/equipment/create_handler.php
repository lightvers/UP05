<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Equipment.php';

require_admin();

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Метод запроса не поддерживается";
    header('Location: /UP/templates/equipment/create.php');
    exit;
}

// Подключение к базе данных
try {
    $db = (new Database())->connect();
    $equipment = new Equipment($db);

    // Получение и валидация данных
    $required_fields = ['name', 'inventory_number', 'type_id', 'status_id'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Поле " . ucfirst(str_replace('_', ' ', $field)) . " обязательно для заполнения.";
            header('Location: /UP/templates/equipment/create.php');
            exit;
        }
    }

    // Подготовка данных
    $equipment->name = trim($_POST['name']);
    $equipment->inventory_number = trim($_POST['inventory_number']);
    $equipment->cost = !empty($_POST['cost']) ? trim($_POST['cost']) : null;
    $equipment->current_classroom_id = !empty($_POST['classroom_id']) ? (int)$_POST['classroom_id'] : null;
    $equipment->responsible_user_id = !empty($_POST['responsible_user_id']) ? (int)$_POST['responsible_user_id'] : null;
    $equipment->temp_responsible_user_id = !empty($_POST['temp_responsible_user_id']) ? (int)$_POST['temp_responsible_user_id'] : null;
    $equipment->status_id = (int)$_POST['status_id'];
    $equipment->model_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
    $equipment->comments = !empty($_POST['description']) ? trim($_POST['description']) : null;

    // Проверка уникальности инвентарного номера
    if ($equipment->inventoryNumberExists($equipment->inventory_number)) {
        $_SESSION['error'] = "Оборудование с таким инвентарным номером уже существует";
        header('Location: /UP/templates/equipment/create.php');
        exit;
    }

    // Создание оборудования
    if ($equipment->create()) {
        $_SESSION['success'] = "Оборудование успешно добавлено!";
        header('Location: /UP/templates/equipment/index.php');
        exit;
    } else {
        throw new Exception("Ошибка при добавлении оборудования в базу данных");
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
    header('Location: /UP/templates/equipment/create.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: /UP/templates/equipment/create.php');
    exit;
}
