<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

// Проверка прав администратора
if (get_current_user_role() !== 'admin') {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Недостаточно прав для выполнения операции'
    ];
    header('Location: index.php');
    exit();
}

$page_title = "Удаление пользователя";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Проверка ID
if ($id <= 0) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Неверный ID пользователя'
    ];
    header('Location: index.php');
    exit();
}

// Получаем данные пользователя
require_once '../../config/database.php';
require_once '../../models/User.php';

try {
    $db = (new Database())->connect();
    $user = new User($db);

    if (!$user->getById($id)) {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Пользователь не найден'
        ];
        header('Location: index.php');
        exit();
    }

    $user_data = [
        'id' => $user->id,
        'username' => $user->username,
        'full_name' => trim("{$user->last_name} {$user->first_name} {$user->middle_name}"),
        'role' => $user->role
    ];

} catch (Exception $e) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => $e->getMessage()
    ];
    header('Location: index.php');
    exit();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($user->delete()) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Пользователь успешно удален'
            ];
        } else {
            throw new Exception('Ошибка при удалении пользователя');
        }
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => $e->getMessage()
        ];
    }
    header('Location: index.php');
    exit();
}
?>

<div class="content-header">
    <h1 class="content-title">Удаление пользователя</h1>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="card border-danger">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle"></i> Подтверждение удаления
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <h5><i class="bi bi-trash"></i> Вы уверены, что хотите удалить этого пользователя?</h5>
            <div class="mt-3">
                <p class="mb-1"><strong>ФИО:</strong> <?= htmlspecialchars($user_data['full_name']) ?></p>
                <p class="mb-1"><strong>Логин:</strong> <?= htmlspecialchars($user_data['username']) ?></p>
                <p class="mb-1"><strong>Роль:</strong> <?= htmlspecialchars($user_data['role']) ?></p>
                <p class="mb-0"><strong>ID:</strong> <?= $user_data['id'] ?></p>
            </div>
        </div>
        
        <form method="post" class="mt-4">
            <input type="hidden" name="id" value="<?= $user_data['id'] ?>">
            
            <div class="d-flex justify-content-between">
                <a href="view.php?id=<?= $user_data['id'] ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Отмена
                </a>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены? Это действие нельзя отменить!')">
                    <i class="bi bi-trash"></i> Подтвердить удаление
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>