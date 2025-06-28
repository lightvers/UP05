<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../models/Consumable.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);

$consumable->id = $_GET['id'];

if ($consumable->delete()) {
    $_SESSION['success'] = "Расходный материал успешно удален";
} else {
    $_SESSION['error'] = "Ошибка при удалении расходного материала";
}

header("Location: index.php");
exit();?>