<?php
require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';
require_login();

if (!isset($_GET['id']) || !isset($_GET['consumable_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../../../models/ConsumableCharacteristic.php';

$db = (new Database())->connect();
$characteristic = new ConsumableCharacteristic($db);

$characteristic->id = $_GET['id'];

if ($characteristic->delete()) {
    $_SESSION['success'] = "Характеристика успешно удалена";
} else {
    $_SESSION['error'] = "Ошибка при удалении характеристики";
}

header("Location: index.php?consumable_id=" . $_GET['consumable_id']);
exit();