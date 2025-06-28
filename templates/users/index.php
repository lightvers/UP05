<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login(); // Важно: требуем авторизацию для доступа к странице
$page_title = "Пользователи";

// Получаем параметры для отображения
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Включаем логирование ошибок в файл
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Напрямую получаем данные через включение обработчика
require_once '../../config/database.php';
require_once '../../models/User.php';

// Инициализируем данные с значениями по умолчанию
$data = [
    'success' => false,
    'message' => '',
    'users' => [],
    'pagination' => [
        'current_page' => 1,
        'per_page' => 10,
        'total_users' => 0,
        'total_pages' => 1
    ],
    'search' => $search,
    'role' => $role,
    'user_role' => get_current_user_role()
];

$debug_info = [];
$has_error = false;
$sql_debug = null;
$sql_params_debug = null;

try {
    $db = (new Database())->connect();
    $debug_info[] = "DB connection successful";

    $user = new User($db);
    $debug_info[] = "User model created";

    // Получаем список ролей для фильтра
    $roles = $user->getAllRoles();
    $debug_info[] = "Roles loaded: " . count($roles);

    // Валидация номера страницы
    if ($page < 1) {
        $page = 1;
    }

    // Получаем общее количество пользователей с учетом фильтров
    $total_users = $user->countAll($search, $role);
    $debug_info[] = "Total users: {$total_users}, search: '{$search}', role: '{$role}'";

    $per_page = 10;
    $total_pages = max(1, ceil($total_users / $per_page));
    $debug_info[] = "Total pages: {$total_pages}";

    // Корректируем номер страницы, если он выходит за пределы
    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
    }

    // Получаем пользователей с пагинацией и фильтрами
    try {
        // Для отладки SQL и параметров
        if (method_exists($user, 'getPaginatedDebug')) {
            // Если реализован специальный метод для отладки
            $debug_result = $user->getPaginatedDebug($page, $per_page, $search, $role);
            $stmt = $debug_result['stmt'];
            $sql_debug = $debug_result['sql'];
            $sql_params_debug = $debug_result['params'];
        } else {
            $stmt = $user->getPaginated($page, $per_page, $search, $role);
            // Попробуем получить SQL и параметры через свойства объекта, если доступны
            if (property_exists($user, 'last_sql')) $sql_debug = $user->last_sql;
            if (property_exists($user, 'last_params')) $sql_params_debug = $user->last_params;
        }
        $debug_info[] = "Got paginated results. Row count: " . $stmt->rowCount();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $debug_info[] = "Fetched users array. Count: " . count($users);
    } catch (Exception $e) {
        $has_error = true;
        $error_message = "Ошибка при получении пользователей: " . $e->getMessage();
        error_log($error_message);
        $debug_info[] = "Error: " . $error_message;
        $users = [];
    }

    // Подготавливаем данные
    $data = [
        'success' => true,
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_users' => $total_users,
            'total_pages' => $total_pages
        ],
        'search' => $search,
        'role' => $role,
        'user_role' => get_current_user_role()
    ];

} catch (Exception $e) {
    $has_error = true;
    $error_message = "Ошибка при загрузке данных пользователей: " . $e->getMessage();
    error_log($error_message);
    $debug_info[] = "Error: " . $error_message;
    $data['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

// Extract data
$users = $data['users'] ?? [];
$pagination = $data['pagination'] ?? [
    'current_page' => 1,
    'per_page' => 10,
    'total_users' => 0,
    'total_pages' => 1
];
$search = $data['search'] ?? '';
$role = $data['role'] ?? '';
$user_role = $data['user_role'] ?? '';

// Функция для создания URL с параметрами
function buildUrl($params = []) {
    $current = $_GET;
    $new = array_merge($current, $params);
    return '?' . http_build_query($new);
}
?>

<div class="content-header">
    <h1 class="content-title">Пользователи</h1>
    <div>
        <?php if($user_role === 'admin'): ?>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_SESSION['alert'])): ?>
<div class="alert alert-<?= $_SESSION['alert']['type'] ?>">
    <i class="bi bi-info-circle-fill me-2"></i>
    <?= $_SESSION['alert']['message'] ?>
</div>
<?php
    unset($_SESSION['alert']);
endif;
?>

<?php if($has_error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Произошла ошибка при загрузке данных пользователей. Обратитесь к администратору.
    <br>
    <small><?= htmlspecialchars($error_message ?? 'Подробности недоступны') ?></small>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-people"></i> Список пользователей
        </div>
        <div class="text-muted small">
            Всего записей: <?= $pagination['total_users'] ?>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <form method="GET" id="search-form" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" id="search-input" placeholder="Поиск по имени или логину..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary" name="search_btn" id="search-btn">
                            <i class="bi bi-search"></i> Найти
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="input-group">
                        <select name="role" id="role-select" class="form-select">
                            <option value="">Все роли</option>
                            <?php foreach($roles as $role_key => $role_name): ?>
                                <option value="<?= $role_key ?>" <?= ($role == $role_key) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-info" name="filter_btn" id="filter-btn">
                            <i class="bi bi-funnel"></i> Фильтровать
                        </button>
                    </div>
                </div>

                <div class="col-md-3 text-end">
                    <a href="index.php" class="btn btn-secondary" id="reset-btn">
                        <i class="bi bi-arrow-counterclockwise"></i> Сбросить фильтры
                    </a>
                </div>
            </form>
        </div>

        <script>
        // Улучшение работы формы поиска
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('search-form');
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');
            const roleSelect = document.getElementById('role-select');
            const filterBtn = document.getElementById('filter-btn');
            const resetBtn = document.getElementById('reset-btn');

            // Отправка формы при нажатии Enter в поле поиска
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchForm.submit();
                }
            });

            // Логирование для отладки
            searchBtn.addEventListener('click', function(e) {
                console.log('Поиск по:', searchInput.value);
            });

            filterBtn.addEventListener('click', function(e) {
                console.log('Фильтр по роли:', roleSelect.value);
            });
        });
        </script>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Логин</th>
                        <th>ФИО</th>
                        <th>Роль</th>
                        <th>Email</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($users)): ?>
                        <?php foreach($users as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['last_name']) ?>
                                <?= htmlspecialchars($row['first_name']) ?>
                                <?= htmlspecialchars($row['middle_name']) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?=
                                    $row['role'] === 'admin' ? 'danger' :
                                    ($row['role'] === 'teacher' ? 'primary' : 'secondary')
                                ?>">
                                    <?=
                                        $row['role'] === 'admin' ? 'Администратор' :
                                        ($row['role'] === 'teacher' ? 'Преподаватель' : 'Сотрудник')
                                    ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= get_base_url() ?>/templates/users/view.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if($user_role === 'admin'): ?>
                                    <a href="<?= get_base_url() ?>/templates/users/edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= get_base_url() ?>/templates/users/delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <?php if(!empty($search) || !empty($role)): ?>
                                    По вашему запросу пользователи не найдены. <a href="index.php">Сбросить фильтры</a>
                                <?php else: ?>
                                    Пользователи не найдены. <?php if($user_role === 'admin'): ?><a href="create.php">Добавить нового пользователя</a><?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <?php if($pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildUrl(['page' => $pagination['current_page'] - 1]) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php
                // Показываем максимум 5 страниц
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage = min($pagination['total_pages'], $startPage + 4);

                if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . buildUrl(['page' => 1]) . '">1</a></li>';
                    if ($startPage > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for($i = $startPage; $i <= $endPage; $i++):
                ?>
                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= buildUrl(['page' => $i]) ?>"><?= $i ?></a>
                </li>
                <?php
                endfor;

                if ($endPage < $pagination['total_pages']) {
                    if ($endPage < $pagination['total_pages'] - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="' . buildUrl(['page' => $pagination['total_pages']]) . '">' . $pagination['total_pages'] . '</a></li>';
                }
                ?>

                <?php if($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= buildUrl(['page' => $pagination['current_page'] + 1]) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

       
</div>

<!-- JavaScript инициализирован в main.js -->
<script>
// Только диагностический вывод
document.addEventListener('DOMContentLoaded', function() {
    // Проверка работы кнопок в консоли
    console.log("Кнопки в таблице пользователей: проверка количества");
    console.log("Количество кнопок просмотра:", document.querySelectorAll('.btn-outline-primary').length);
    console.log("Количество кнопок редактирования:", document.querySelectorAll('.btn-outline-secondary').length);
    console.log("Количество кнопок удаления:", document.querySelectorAll('.delete-btn').length);
});
</script>

<?php require_once '../../includes/footer.php'; ?>
