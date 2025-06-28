<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$type = $_GET['type'] ?? '';

if(strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

if($type === 'consumables') {
    $stmt = $pdo->prepare("
        SELECT id, name, quantity, cost 
        FROM consumables 
        WHERE name LIKE :query
        ORDER BY name
        LIMIT 10
    ");
    $stmt->execute([':query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT id, name, inventory_number, cost 
        FROM equipment 
        WHERE name LIKE :query
        ORDER BY name
        LIMIT 10
    ");
    $stmt->execute([':query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($results);