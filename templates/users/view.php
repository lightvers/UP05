<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login(); // Требуем авторизацию для доступа к странице
$page_title = "Просмотр пользователя";

// Fetch data from backend API
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$api_url = '../../templates/users/view_handler.php?id=' . $id;

// Проверяем доступность обработчика
if (!file_exists('../../templates/users/view_handler.php')) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Ошибка: Файл обработчика view_handler.php не найден'
    ];
    header('Location: index.php');
    exit();
}

// Вызываем обработчик напрямую вместо file_get_contents
require_once '../../config/database.php';
require_once '../../models/User.php';

$db = (new Database())->connect();
$user_model = new User($db);

// Проверяем существование пользователя
if (!$user_model->getById($id)) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Пользователь не найден'
    ];
    header('Location: index.php');
    exit();
}

// Получаем оборудование пользователя
$equipment = $user_model->getAssignedEquipment()->fetchAll(PDO::FETCH_ASSOC);

// Создаем объект с данными
$data = [
    'success' => true,
    'user' => [
        'id' => $user_model->id,
        'username' => $user_model->username,
        'last_name' => $user_model->last_name,
        'first_name' => $user_model->first_name,
        'middle_name' => $user_model->middle_name,
        'role' => $user_model->role,
        'email' => $user_model->email,
        'phone' => $user_model->phone,
    ],
    'equipment' => $equipment,
    'current_user_id' => $_SESSION['user_id'],
    'current_user_role' => get_current_user_role()
];

// Check if request was successful
if (!$data['success']) {
    $_SESSION['error'] = $data['message'] ?? 'Пользователь не найден';
    header('Location: index.php');
    exit();
}

// Extract data
$user = $data['user'];
$equipment = $data['equipment'];
$current_user_id = $data['current_user_id'];
$current_user_role = $data['current_user_role'];
?>

<div class="content-header">
    <h1 class="content-title">Просмотр пользователя</h1>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <?php if($current_user_role === 'admin' || $current_user_id == $user['id']): ?>
        <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-primary ms-2">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-badge"></i> Основная информация
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Логин:</div>
                    <div class="col-md-8"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">ФИО:</div>
                    <div class="col-md-8">
                        <?= htmlspecialchars($user['last_name']) ?>
                        <?= htmlspecialchars($user['first_name']) ?>
                        <?= htmlspecialchars($user['middle_name']) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Роль:</div>
                    <div class="col-md-8">
                        <span class="badge bg-<?=
                            $user['role'] === 'admin' ? 'danger' :
                            ($user['role'] === 'teacher' ? 'primary' : 'secondary')
                        ?>">
                            <?=
                                $user['role'] === 'admin' ? 'Администратор' :
                                ($user['role'] === 'teacher' ? 'Преподаватель' : 'Сотрудник')
                            ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Телефон:</div>
                    <div class="col-md-8"><?= htmlspecialchars($user['phone']) ?></div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pc-display"></i> Закрепленное оборудование
            </div>
            <div class="card-body">
                <?php if(!empty($equipment)): ?>
                    <div class="list-group">
                        <?php foreach($equipment as $row): ?>
                        <a href="../../templates/equipment/view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
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
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Нет закрепленного оборудования</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
