<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';

require_login();

$page_title = "Инвентаризации";

// Параметры
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$sort = $_GET['sort'] ?? 'start_date';
$order = $_GET['order'] ?? 'desc';

try {
    $db = (new Database())->connect();
    $inventory = new Inventory($db);

    // Получаем данные
    $total = $inventory->countAll($search, $status);
    $stmt = $inventory->getPaginated($page, $perPage, $search, $status, '', $sort, $order);
    $inventories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = max(1, ceil($total / $perPage));

} catch (Exception $e) {
    die('<div class="alert alert-danger">Ошибка: '.htmlspecialchars($e->getMessage()).'</div>');
}

function buildUrl($params = []) {
    return '?'.http_build_query(array_merge($_GET, $params));
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
    <div class="card-body">
        <form method="GET" class="mb-4 row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Поиск..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>>Запланирована</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Завершена</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Фильтровать</button>
            </div>
            <div class="col-md-3">
                <a href="index.php" class="btn btn-outline-secondary w-100">Сбросить</a>
            </div>
        </form>

        <?php if (!empty($inventories)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                        <th>Дата начала</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventories as $inv): ?>
                    <tr>
                        <td><?= (int)$inv['id'] ?></td>
                        <td><?= htmlspecialchars($inv['name']) ?></td>
                        <td><?= date('d.m.Y', strtotime($inv['start_date'])) ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $inv['status'] === 'completed' ? 'success' : 
                                ($inv['status'] === 'in_progress' ? 'warning' : 'secondary')
                            ?>">
                                <?= match($inv['status']) {
                                    'completed' => 'Завершена',
                                    'in_progress' => 'В процессе',
                                    default => 'Запланирована'
                                } ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="view.php?id=<?= (int)$inv['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?= (int)$inv['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?= (int)$inv['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   title="Удалить"
                                   onclick="return confirm('Вы уверены? Инвентаризация будет удалена!')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Навигация">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildUrl(['page' => 1]) ?>" aria-label="Первая">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= buildUrl(['page' => $i]) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildUrl(['page' => $totalPages]) ?>" aria-label="Последняя">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info text-center py-4">
            <i class="bi bi-info-circle me-2"></i> Инвентаризации не найдены
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Добавляем обработчик для всех кнопок удаления
document.querySelectorAll('a[href*="delete.php"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Вы точно хотите удалить эту инвентаризацию?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>