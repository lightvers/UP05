<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$database = new Database();
$pdo = $database->connect();

try {
    $stmt = $pdo->query("SELECT id, name, inventory_number, cost FROM equipment ORDER BY name");
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($equipment);
} catch (PDOException $e) {
    echo json_encode([]);
}