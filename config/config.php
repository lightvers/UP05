<?php
// Базовый URL сайта
$base_url = '/UP';  // Возвращаем базовый URL в /UP

// Функция для получения базового URL
function get_base_url() {
    global $base_url;
    return $base_url;
}
