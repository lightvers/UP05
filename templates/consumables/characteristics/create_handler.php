<?php
require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['consumable_id'])) {
    $_SESSION['error'] = "Неверный запрос";
    header("Location: ../index.php");
    exit();
}

// Валидация входных данных
if (empty($_POST['name']) || empty($_POST['value'])) {
    $_SESSION['error'] = "Все поля обязательны для заполнения";
    header("Location: create.php?consumable_id=" . $_POST['consumable_id']);
    exit();
}

require_once '../../../models/ConsumableCharacteristic.php';

try {
    $db = (new Database())->connect();
    $characteristic = new ConsumableCharacteristic($db);

    $characteristic->consumable_id = (int)$_POST['consumable_id'];
    $characteristic->name = trim($_POST['name']);
    $characteristic->value = trim($_POST['value']);

    if ($characteristic->create()) {
        $_SESSION['success'] = "Характеристика успешно добавлена";
    } else {
        $_SESSION['error'] = "Ошибка при добавлении характеристики";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Ошибка: " . $e->getMessage();
}

header("Location: index.php?consumable_id=" . $_POST['consumable_id']);
exit();
?>