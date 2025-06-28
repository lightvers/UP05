<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Equipment.php';
require_once '../../models/Classroom.php';

require_login();
$page_title = "Редактирование пользователя";

// Initialize variables
$user = null;
$error = null;
$success = false;
$roles = [];
$assignedEquipment = [];
$assignedClassrooms = [];

// Get user ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $db = (new Database())->connect();
    $userModel = new User($db);
    $equipmentModel = new Equipment($db);
    $classroomModel = new Classroom($db);

    // Get user data
    if (!$userModel->getById($id)) {
        throw new Exception('Пользователь не найден');
    }

    // Get additional data
    $roles = $userModel->getAllRoles();
    $assignedEquipment = $equipmentModel->getByResponsibleUser($id);
    $assignedClassrooms = $classroomModel->getByResponsibleUser($id);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Basic info
        $userModel->username = trim($_POST['username'] ?? '');
        $userModel->role = trim($_POST['role'] ?? '');
        $userModel->email = trim($_POST['email'] ?? '');
        $userModel->last_name = trim($_POST['last_name'] ?? '');
        $userModel->first_name = trim($_POST['first_name'] ?? '');
        $userModel->middle_name = trim($_POST['middle_name'] ?? '');
        $userModel->phone = trim($_POST['phone'] ?? '');

        // Password change (if provided)
        $new_password = trim($_POST['new_password'] ?? '');
        if (!empty($new_password)) {
            $userModel->password = $new_password;
            $password_changed = $userModel->updatePassword();
        }

        // Update user
        if ($userModel->update()) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Данные пользователя успешно обновлены'
            ];
            header('Location: view.php?id=' . $id);
            exit();
        } else {
            throw new Exception('Не удалось обновить данные пользователя');
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Prepare user data for form
$user = [
    'id' => $userModel->id,
    'username' => $userModel->username,
    'role' => $userModel->role,
    'email' => $userModel->email,
    'last_name' => $userModel->last_name,
    'first_name' => $userModel->first_name,
    'middle_name' => $userModel->middle_name,
    'phone' => $userModel->phone
];
?>

<div class="content-header">
    <h1 class="content-title">Редактирование пользователя</h1>
    <div>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> К списку
        </a>
        <a href="view.php?id=<?= $id ?>" class="btn btn-outline-primary ms-2">
            <i class="bi bi-eye"></i> Просмотр
        </a>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-gear"></i> Основные данные
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Логин *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="role" class="form-label">Роль *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Выберите роль --</option>
                                <?php foreach ($roles as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $user['role'] === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="last_name" class="form-label">Фамилия *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="first_name" class="form-label">Имя *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="middle_name" class="form-label">Отчество</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                   value="<?= htmlspecialchars($user['middle_name']) ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                        
                        <div class="col-12">
                            <hr>
                            <h5>Смена пароля</h5>
                            <div class="alert alert-info">
                                Оставьте поле пустым, если не нужно менять пароль
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Новый пароль</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Минимум 6 символов</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-laptop"></i> Закрепленное оборудование
            </div>
            <div class="card-body">
                <?php if (!empty($assignedEquipment)): ?>
                    <ul class="list-group">
                        <?php foreach ($assignedEquipment as $item): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($item['name']) ?> 
                                <small class="text-muted">(<?= htmlspecialchars($item['inventory_number']) ?>)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        Нет закрепленного оборудования
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-building"></i> Закрепленные аудитории
            </div>
            <div class="card-body">
                <?php if (!empty($assignedClassrooms)): ?>
                    <ul class="list-group">
                        <?php foreach ($assignedClassrooms as $item): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($item['name']) ?> 
                                <small class="text-muted">(№ <?= htmlspecialchars($item['number']) ?>)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        Нет закрепленных аудиторий
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validate required fields
        const requiredFields = ['username', 'role', 'last_name', 'first_name'];
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Validate password length if provided
        const password = document.getElementById('new_password');
        if (password.value.trim() && password.value.trim().length < 6) {
            password.classList.add('is-invalid');
            valid = false;
        } else {
            password.classList.remove('is-invalid');
        }
        
        if (!valid) {
            e.preventDefault();
            alert('Пожалуйста, заполните все обязательные поля корректно');
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>