<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_login();

$page_title = "Главная панель";
require_once 'models/Equipment.php';
require_once 'models/Classroom.php';
require_once 'models/User.php';
require_once 'models/Inventory.php';

$db = (new Database())->connect();
$equipment_count = (new Equipment($db))->count();
$classroom_count = (new Classroom($db))->count();
$user_count = (new User($db))->count();
?>

<div class="content-header">
    <h1 class="content-title">Главная панель</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pc-display"></i> Оборудование
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $equipment_count ?></h2>
                <p class="text-muted">единиц оборудования</p>
                <a href="<?= get_base_url() ?>/templates/equipment/index.php" class="btn btn-primary btn-sm">
                    Просмотреть <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building"></i> Аудитории
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $classroom_count ?></h2>
                <p class="text-muted">аудиторий</p>
                <a href="<?= get_base_url() ?>/templates/classrooms/index.php" class="btn btn-primary btn-sm">
                    Просмотреть <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-people"></i> Пользователи
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $user_count ?></h2>
                <p class="text-muted">пользователей</p>
                <a href="<?= get_base_url() ?>/templates/users/index.php" class="btn btn-primary btn-sm">
                    Просмотреть <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if(get_current_user_role() === 'admin'): ?>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Последние инвентаризации
            </div>
            <div class="card-body">
                <?php
                $inventory = (new Inventory($db))->getRecent(3);
                if($inventory->rowCount() > 0):
                ?>
                    <div class="list-group">
                        <?php while($row = $inventory->fetch(PDO::FETCH_ASSOC)): ?>
                        <a href="<?= get_base_url() ?>/templates/inventory/view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                                <small><?= date('d.m.Y', strtotime($row['start_date'])) ?></small>
                            </div>
                            <small class="text-muted">Статус:
                                <span class="badge bg-<?=
                                    $row['status'] === 'completed' ? 'success' :
                                    ($row['status'] === 'in_progress' ? 'warning' : 'secondary')
                                ?>">
                                    <?=
                                        $row['status'] === 'completed' ? 'Завершена' :
                                        ($row['status'] === 'in_progress' ? 'В процессе' : 'Запланирована')
                                    ?>
                                </span>
                            </small>
                        </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Нет данных об инвентаризациях</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle"></i> Оборудование на ремонте
            </div>
            <div class="card-body">
                <?php
                $equipment = (new Equipment($db))->getByStatus('На ремонте', 3);
                if($equipment->rowCount() > 0):
                ?>
                    <div class="list-group">
                        <?php while($row = $equipment->fetch(PDO::FETCH_ASSOC)): ?>
                        <a href="<?= get_base_url() ?>/templates/equipment/view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                                <small><?= htmlspecialchars($row['inventory_number']) ?></small>
                            </div>
                            <small class="text-muted">Аудитория: <?= htmlspecialchars($row['classroom_name'] ?? 'Не указана') ?></small>
                        </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Нет оборудования на ремонте</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once  'includes/footer.php'; ?>
