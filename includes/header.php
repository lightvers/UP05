<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$page_title = isset($page_title) ? $page_title : 'Система учета оборудования';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <?php require_once __DIR__ . '/../config/config.php'; ?>
    <link href="<?= get_base_url() ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
