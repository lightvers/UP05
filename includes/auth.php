<?php
// Убедимся, что сессия запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Проверяет, авторизован ли пользователь
 * @return bool Возвращает true если пользователь авторизован, иначе false
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Требует авторизации пользователя или перенаправляет на страницу входа
 * @return void
 */
function require_login() {
    if(!is_logged_in()) {
        // Сохраняем текущий URL для перенаправления после входа
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

        // Относительный путь к login.php
        $depth = substr_count(dirname($_SERVER['PHP_SELF']), '/');
        $path = str_repeat('../', $depth > 1 ? $depth - 1 : 0);

        header("Location: {$path}login.php");
        exit();
    }
}

/**
 * Требует наличия прав администратора или перенаправляет на главную
 * @return void
 */
function require_admin() {
    require_login();
    if($_SESSION['user_role'] !== 'admin') {
        // Относительный путь к index.php
        $depth = substr_count(dirname($_SERVER['PHP_SELF']), '/');
        $path = str_repeat('../', $depth > 1 ? $depth - 1 : 0);

        header("Location: {$path}index.php");
        exit();
    }
}

/**
 * Получает ID текущего пользователя
 * @return int|null ID пользователя или null, если пользователь не авторизован
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Получает роль текущего пользователя
 * @return string|null Роль пользователя или null, если пользователь не авторизован
 */
function get_current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Получает полное имя текущего пользователя
 * @return string Полное имя пользователя или пустую строку
 */
function get_current_user_fullname() {
    if (!isset($_SESSION['user_full_name'])) {
        return '';
    }
    return $_SESSION['user_full_name'];
}
?>
