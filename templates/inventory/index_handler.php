<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$page_title = "Инвентаризации";

// Получаем параметры из GET
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'start_date';
$order = $_GET['order'] ?? 'desc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Формируем URL для API
$api_url = '/UP/handlers/inventory/index_handler.php?' . http_build_query([
    'search' => $search,
    'status' => $status,
    'sort' => $sort,
    'order' => $order,
    'page' => $page
]);

// Получаем данные
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = file_get_contents($api_url, false, $context);
$data = json_decode($response, true);

if (!$data || !$data['success']) {
    die('<div class="alert alert-danger">Ошибка загрузки данных: ' . 
        ($data['message'] ?? 'Неизвестная ошибка') . '</div>');
}

function buildUrl($params = []) {
    $current = $_GET;
    $new = array_merge($current, $params);
    return '?' . http_build_query($new);
}
?>

<div class="content-header">
    <h1 class="content-title">Инвентаризации</h1>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-clipboard-check"></i> Список инвентаризаций
        </div>
        <div class="text-muted small">
            Всего записей: <?= $data['pagination']['total_inventories'] ?>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Все статусы</option>
                        <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>>Запланирована</option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Завершена</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-funnel"></i> Фильтровать
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Сбросить
                    </a>
                </div>
            </form>
        </div>

        <?php if(!empty($data['inventories'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="<?= buildUrl(['sort' => 'name', 'order' => ($sort === 'name' && $order === 'asc') ? 'desc' : 'asc']) ?>">
                                    Название <?= $sort === 'name' ? '<i class="bi bi-arrow-'.($order === 'asc'?'up':'down').'"></i>' : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= buildUrl(['sort' => 'start_date', 'order' => ($sort === 'start_date' && $order === 'asc') ? 'desc' : 'asc']) ?>">
                                    Дата начала <?= $sort === 'start_date' ? '<i class="bi bi-arrow-'.($order === 'asc'?'up':'down').'"></i>' : '' ?>
                                </a>
                            </th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['inventories'] as $inv): ?>
                        <tr>
                            <td><?= $inv['id'] ?></td>
                            <td><?= htmlspecialchars($inv['name']) ?></td>
                            <td><?= date('d.m.Y', strtotime($inv['start_date'])) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $inv['status'] === 'completed' ? 'success' : 
                                    ($inv['status'] === 'in_progress' ? 'warning' : 'secondary')
                                ?>">
                                    <?= 
                                        $inv['status'] === 'completed' ? 'Завершена' : 
                                        ($inv['status'] === 'in_progress' ? 'В процессе' : 'Запланирована')
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="view.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(get_current_user_role() === 'admin'): ?>
                                    <a href="edit.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить инвентаризацию?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <?php if($data['pagination']['total_pages'] > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildUrl(['page' => $page - 1]) ?>">
                            &laquo;
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $data['pagination']['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= buildUrl(['page' => $i]) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $data['pagination']['total_pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildUrl(['page' => $page + 1]) ?>">
                            &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Инвентаризации не найдены
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>