<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

$page_title = "Оборудование";
require_once 'index_handler.php';

$controller = new EquipmentController();
$data = $controller->index();
?>

<div class="content-header">
    <h1 class="content-title">Оборудование</h1>
    <div>
        <?php if(get_current_user_role() === 'admin'): ?>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Добавить
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> Список оборудования
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Поиск..." value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status_id">
                        <option value="">Все статусы</option>
                        <?php foreach($data['statuses'] as $status): ?>
                            <option value="<?= $status['id'] ?>" <?= ($data['filters']['status_id'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="classroom_id">
                        <option value="">Все аудитории</option>
                        <?php foreach($data['classrooms'] as $classroom): ?>
                            <option value="<?= $classroom['id'] ?>" <?= ($data['filters']['classroom_id'] ?? '') == $classroom['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classroom['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Фильтр
                    </button>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Наименование</th>
                        <th>Инв. номер</th>
                        <th>Аудитория</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($data['equipment'])): ?>
                        <?php foreach($data['equipment'] as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['inventory_number']) ?></td>
                            <td><?= htmlspecialchars($row['classroom_name'] ?? 'Не указана') ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $row['status_name'] === 'На ремонте' ? 'warning' : 
                                    ($row['status_name'] === 'Сломано' ? 'danger' : 'success') 
                                ?>">
                                    <?= htmlspecialchars($row['status_name'] ?? 'Не указан') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary" title="Просмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(get_current_user_role() === 'admin'): ?>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" title="Удалить" onclick="return confirm('Вы уверены?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Оборудование не найдено</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>