<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

$page_title = "Аудитории";
require_once 'index_handler.php';

$controller = new ClassroomController();
$classrooms = $controller->index();
$responsibleUsers = $controller->getResponsibleUsers();

// Получаем текущие значения фильтров
$search = $_GET['search'] ?? '';
$responsible_id = $_GET['responsible_id'] ?? '';
$min_equipment = $_GET['min_equipment'] ?? '';
?>

<div class="content-header">
    <h1 class="content-title">Аудитории</h1>
    <div>
        <?php if(get_current_user_role() === 'admin'): ?>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-funnel"></i> Фильтрация аудиторий
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Поиск по названию</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Введите название или краткое название..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label for="responsible_id" class="form-label">Ответственный</label>
                <select class="form-select" id="responsible_id" name="responsible_id">
                    <option value="">Все ответственные</option>
                    <?php while($user = $responsibleUsers->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $user['id'] ?>" <?= $responsible_id == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['last_name'] . ' ' . $user['first_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="min_equipment" class="form-label">Мин. количество оборудования</label>
                <input type="number" class="form-control" id="min_equipment" name="min_equipment" min="0" value="<?= htmlspecialchars($min_equipment) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Применить
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Сбросить
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-building"></i> Список аудиторий
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Наименование</th>
                        <th>Краткое название</th>
                        <th>Оборудование</th>
                        <th>Ответственный</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($classrooms->rowCount() > 0): ?>
                        <?php while($row = $classrooms->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['short_name']) ?></td>
                            <td>
                                <span class="badge bg-primary"><?= $row['equipment_count'] ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['responsible_last_name'] ?? '') ?>
                                <?= htmlspecialchars($row['responsible_first_name'] ?? '') ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="/UP/templates/classrooms/view.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if(get_current_user_role() === 'admin'): ?>
                                    <a href="/UP/templates/classrooms/edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/UP/templates/classrooms/delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Вы уверены?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Аудитории не найдены</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
