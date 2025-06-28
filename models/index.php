<?php
/**
 * Индексный файл для удобного подключения всех моделей сразу
 */

// Убедимся, что константа не определена, чтобы избежать повторного включения
if (!defined('MODELS_LOADED')) {
    define('MODELS_LOADED', true);

    require_once __DIR__ . '/User.php';
    require_once __DIR__ . '/Equipment.php';
    require_once __DIR__ . '/Classroom.php';
    require_once __DIR__ . '/Inventory.php';
    require_once __DIR__ . '/Consumable.php';
    require_once __DIR__ . '/ReferenceItem.php';
}
?>
