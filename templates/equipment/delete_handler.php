<?php
require_once '../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

if (!isset($_POST['id'])) {
    $_SESSION['error'] = "Не указан ID оборудования";
    header("Location: index.php");
    exit();
}

require_once '../../config/database.php';
require_once '../../models/Equipment.php';

$db = (new Database())->connect();
$equipment = new Equipment($db);

$id = $_POST['id'];
$equipment->id = $id;

if ($equipment->delete()) {
    $_SESSION['success'] = "Оборудование успешно удалено";
} else {
    $_SESSION['error'] = "Ошибка при удалении оборудования";
}

header("Location: index.php");
exit();
?>