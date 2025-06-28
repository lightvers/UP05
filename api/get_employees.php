<?php
require_once '../includes/database.php';
header('Content-Type: application/json');

$database = new Database();
$pdo = $database->connect();

$stmt = $pdo->query("SELECT id, last_name, first_name, middle_name FROM users WHERE role != 'admin'");
$employees = $stmt->fetchAll();

echo json_encode($employees);
?>