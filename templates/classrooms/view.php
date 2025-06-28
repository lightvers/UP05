<?php
require_once  '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /UP/templates/classrooms/index.php');
    exit();
}

$page_title = "Просмотр аудитории";
require_once  '../../models/Classroom.php';
require_once '../../models/User.php';

$db = (new Database())->connect();
$classroom = new Classroom($db);
$classroom->getById($_GET['id']);

if(!$classroom->id) {
    header('Location: /UP/templates/classrooms/index.php');
    exit();
}

// Получаем ответственного пользователя
$responsible_user = null;
if($classroom->responsible_user_id) {
    $user = new User($db);
    $user->getById($classroom->responsible_user_id);
    $responsible_user = $user;
}

// Получаем оборудование в аудитории
$equipment = $classroom->getEquipment($classroom->id);
?>

<div class="content-header">
    <h1 class="content-title">Просмотр аудитории</h1>
    <div>
            <a href="/UP/templates/classrooms/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <?php if(get_current_user_role() === 'admin'): ?>
        <a href="/UP/templates/classrooms/edit.php?id=<?= $classroom->id ?>" class="btn btn-primary ms-2">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <?php endif; ?>
        </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Основная информация
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Наименование:</div>
                    <div class="col-md-8"><?= htmlspecialchars($classroom->name) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Краткое название:</div>
                    <div class="col-md-8"><?= htmlspecialchars($classroom->short_name) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Ответственный:</div>
                    <div class="col-md-8">
                        <?php if($responsible_user): ?>
                           <a href="/UP/templates/users/view.php?id=<?= $responsible_user->id ?>">
                            <?= htmlspecialchars($responsible_user->last_name) ?> 
                            <?= htmlspecialchars($responsible_user->first_name) ?>
                        </a>
                        <?php else: ?>
                            Не назначен
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pc-display"></i> Оборудование в аудитории
            </div>
            <div class="card-body">
                <?php if($equipment->rowCount() > 0): ?>
                    <div class="list-group">
                        <?php while($row = $equipment->fetch(PDO::FETCH_ASSOC)): ?>
                        <a href="/UP/templates/equipment/view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                                <small><?= htmlspecialchars($row['inventory_number']) ?></small>
                            </div>
                            <small class="text-muted">Статус: 
                                <span class="badge bg-<?= 
                                    $row['status_name'] === 'На ремонте' ? 'warning' : 
                                    ($row['status_name'] === 'Сломано' ? 'danger' : 'success') 
                                ?>">
                                    <?= htmlspecialchars($row['status_name']) ?>
                                </span>
                            </small>
                        </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">В аудитории нет оборудования</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>