<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

require_once '../../config/database.php';
require_once '../../models/Equipment.php';
require_once '../../models/Classroom.php';
require_once '../../models/ReferenceItem.php';
require_once '../../models/User.php'; // Add this line

$db = (new Database())->connect();
$classrooms = (new Classroom($db))->getAll();
$statuses = (new ReferenceItem($db))->getByType('status');
$types = (new ReferenceItem($db))->getByType('equipment_type');
$responsibles = (new User($db))->getAll(); // Add this line

// Получаем данные и ошибки из сессии, если они есть
$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Очищаем данные сессии после использования
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);

$page_title = "Добавить пользователя";
?>

<div class="content-header">
    <h1 class="content-title">Добавить пользователя</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-plus"></i> Новый пользователь
    </div>
    <div class="card-body">
        <?php if(isset($_SESSION['alert'])): ?>
        <div class="alert alert-<?= $_SESSION['alert']['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['alert']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
            // Очищаем сообщение после отображения
            unset($_SESSION['alert']);
        endif; ?>

        <form method="POST" action="create_handler.php" id="user-form">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label required-field">Логин</label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               id="username" name="username"
                               value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
                        <?php if(isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required-field">Пароль</label>
                        <div class="input-group">
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#password">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if(isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label required-field">Роль</label>
                        <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                                id="role" name="role" required>
                            <option value="admin" <?= ($form_data['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Администратор</option>
                            <option value="teacher" <?= ($form_data['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Преподаватель</option>
                            <option value="employee" <?= ($form_data['role'] ?? '') === 'employee' ? 'selected' : '' ?>>Сотрудник</option>
                        </select>
                        <?php if(isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['role']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="last_name" class="form-label required-field">Фамилия</label>
                        <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                               id="last_name" name="last_name"
                               value="<?= htmlspecialchars($form_data['last_name'] ?? '') ?>" required>
                        <?php if(isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="first_name" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($form_data['first_name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Отчество</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name"
                               value="<?= htmlspecialchars($form_data['middle_name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Адрес</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($form_data['address'] ?? '') ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Сохранить
                </button>
                <a href="/UP/templates/users/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.querySelector(this.getAttribute('toggle'));
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.querySelector('i').classList.toggle('bi-eye');
    this.querySelector('i').classList.toggle('bi-eye-slash');
});

// Form validation
document.getElementById('user-form').addEventListener('submit', function(e) {
    const requiredFields = ['username', 'password', 'last_name', 'role'];
    let isValid = true;

    requiredFields.forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
