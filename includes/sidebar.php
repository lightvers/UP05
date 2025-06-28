<?php
require_once 'auth.php';

// Функция для проверки активного пункта меню
function isActiveMenu($path) {
    $current_script = $_SERVER['PHP_SELF'];
    // Для главной страницы
    if ($path === '/UP/index.php' && ($current_script === '/UP/index.php' || $current_script === '/UP/')) {
        return true;
    }
    // Для всех остальных
    return strpos($current_script, $path) !== false;
}
?>
<div class="col-md-3 col-lg-2 d-md-block sidebar">
    <div class="sidebar-brand">
        <span>Учет оборудования</span>
    </div>
    <ul class="sidebar-nav">
         <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/index.php') ? 'active' : '' ?>">
                <i class="bi bi-house-door sidebar-nav-icon"></i> Главная
            </a>
        </li>
        <?php endif; ?>
         <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/equipment/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/equipment/') ? 'active' : '' ?>">
                <i class="bi bi-pc-display sidebar-nav-icon"></i> Оборудование
            </a>
        </li>
        <?php endif; ?>
         <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/consumables/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/consumables/') ? 'active' : '' ?>">
                <i class="bi bi-box-seam sidebar-nav-icon"></i> Расходные материалы
            </a>
        </li>
        <?php endif; ?>
         <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/classrooms/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/classrooms/') ? 'active' : '' ?>">
                <i class="bi bi-building sidebar-nav-icon"></i> Аудитории
            </a>
        </li>
        <?php endif; ?>
         <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/users/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/users/') ? 'active' : '' ?>">
                <i class="bi bi-people sidebar-nav-icon"></i> Пользователи
            </a>
        </li>
        <?php endif; ?>
        <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/inventory/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/inventory/') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check sidebar-nav-icon"></i> Инвентаризация
            </a>
        </li>
        <?php endif; ?>
        <?php if(is_logged_in() && $_SESSION['user_role'] === 'admin'): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/reports/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/reports/') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text sidebar-nav-icon"></i> Отчеты
            </a>
        </li>
        <?php endif; ?>
        <?php if(is_logged_in() && $_SESSION['user_role'] === 'admin'): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/templates/references/index.php" class="sidebar-nav-link <?= isActiveMenu('/UP/templates/references/') ? 'active' : '' ?>">
                <i class="bi bi-book sidebar-nav-icon"></i> Справочники
            </a>
        </li>
        <?php endif; ?>
        <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item mt-auto">
            <a href="/UP/profile.php" class="sidebar-nav-link <?= isActiveMenu('/UP/profile.php') ? 'active' : '' ?>">
                <i class="bi bi-person sidebar-nav-icon"></i> Профиль
            </a>
        </li>
        <?php endif; ?>
        <?php if(is_logged_in()): ?>
        <li class="sidebar-nav-item">
            <a href="/UP/logout.php" class="sidebar-nav-link">
                <i class="bi bi-box-arrow-right sidebar-nav-icon"></i> Выход
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>