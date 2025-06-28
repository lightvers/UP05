<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../models/Consumable.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);

// Заполняем данные
$consumable->id = $_POST['id'];
$consumable->name = $_POST['name'];
$consumable->type_id = !empty($_POST['type_id']) ? $_POST['type_id'] : null;
$consumable->equipment_id = !empty($_POST['equipment_id']) ? $_POST['equipment_id'] : null;
$consumable->receipt_date = !empty($_POST['receipt_date']) ? $_POST['receipt_date'] : null;
$consumable->description = !empty($_POST['description']) ? $_POST['description'] : null;

if ($consumable->update()) {
    $_SESSION['success'] = "Расходный материал успешно обновлен";
} else {
    $_SESSION['error'] = "Ошибка при обновлении расходного материала";
}

header("Location: view.php?id=" . $_POST['id']);
exit();?>