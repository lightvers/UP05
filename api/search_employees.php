<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';

if(strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, last_name, first_name, middle_name 
    FROM users 
    WHERE CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE :query
    ORDER BY last_name, first_name
    LIMIT 10
");

$stmt->execute([':query' => "%$query%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);