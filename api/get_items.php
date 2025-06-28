<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$database = new Database();
$pdo = $database->connect();

if ($type == 'consumables') {
    $stmt = $pdo->query("SELECT id, name FROM consumables");
} else {
    $stmt = $pdo->query("SELECT id, name, inventory_number FROM equipment");
}

$items = $stmt->fetchAll();
echo json_encode($items);
?>