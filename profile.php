<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_login();

$page_title = "Профиль пользователя";
require_once 'models/User.php';
require_once 'models/Equipment.php';

$db = (new Database())->connect();
$user = new User($db);
$user->getById($_SESSION['user_id']);

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление основной информации
    if (isset($_POST['update_profile'])) {
        $user->email = $_POST['email'] ?? '';
        $user->phone = $_POST['phone'] ?? '';
        

        if ($user->updateProfile()) {
            $success = "Профиль успешно обновлен";
        } else {
            $error = "Ошибка при обновлении профиля";
        }
    }

    // Обновление пароля
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Проверка текущего пароля
        if (!$user->login($user->username, $current_password)) {
            $error = "Текущий пароль неверный";
        } elseif ($new_password !== $confirm_password) {
            $error = "Новые пароли не совпадают";
        } elseif (strlen($new_password) < 6) {
            $error = "Пароль должен содержать минимум 6 символов";
        } else {
            $user->password = $new_password;
            if ($user->updatePassword()) {
                $success = "Пароль успешно изменен";
            } else {
                $error = "Ошибка при изменении пароля";
            }
        }
    }
}
?>

<div class="content-header">
    <h1 class="content-title">Профиль пользователя</h1>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person"></i> Основная информация
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user->username ?? '') ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ФИО</label>
                        <input type="text" class="form-control"
                               value="<?= htmlspecialchars(trim(($user->last_name ?? '') . ' ' . ($user->first_name ?? '') . ' ' . ($user->middle_name ?? ''))) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($user->email ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= htmlspecialchars($user->phone ?? '') ?>">
                    </div>

                  

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Сохранить
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock"></i> Безопасность
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Текущий пароль</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key"></i> Изменить пароль
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-pc-display"></i> Закрепленное оборудование
            </div>
            <div class="card-body">
                <?php
                if (isset($user->id) && !empty($user->id)) {
                    $equipment = new Equipment($db);
                    $assigned_equipment = $equipment->getByResponsibleUser($user->id);

                    if ($assigned_equipment && $assigned_equipment->rowCount() > 0):
                    ?>
                        <div class="list-group">
                            <?php while($row = $assigned_equipment->fetch(PDO::FETCH_ASSOC)): ?>
                            <a href="/UP/templates/equipment/view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($row['name'] ?? 'Нет данных') ?></h6>
                                    <small><?= htmlspecialchars($row['inventory_number'] ?? 'Б/Н') ?></small>
                                </div>
                                <small class="text-muted">Статус:
                                    <span class="badge bg-<?=
                                        isset($row['status_name']) && $row['status_name'] === 'На ремонте' ? 'warning' :
                                        (isset($row['status_name']) && $row['status_name'] === 'Сломано' ? 'danger' : 'success')
                                    ?>">
                                        <?= htmlspecialchars($row['status_name'] ?? 'Не указан') ?>
                                    </span>
                                </small>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Нет закрепленного оборудования</p>
                    <?php endif;
                } else {
                    echo '<p class="text-muted mb-0">Необходимо авторизоваться для просмотра оборудования</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
