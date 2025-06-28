[file name]: view.php
[file content begin]
<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';
require_once __DIR__ . '/../../models/User.php';

require_login();

$page_title = "Просмотр инвентаризации";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    try {
        $db = (new Database())->connect();
        $inventory = new Inventory($db);
        
        if (!$inventory->getById($id)) {
            throw new Exception('Инвентаризация не найдена');
        }
        
        $new_status = $_POST['status'] ?? '';
        if (!in_array($new_status, ['planned', 'in_progress', 'completed'])) {
            throw new Exception('Неверный статус');
        }
        
        $inventory->status = $new_status;
        if ($inventory->update()) {
            $_SESSION['success'] = 'Статус инвентаризации успешно обновлен';
            header("Location: view.php?id=$id");
            exit;
        } else {
            throw new Exception('Не удалось обновить статус');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

try {
    $db = (new Database())->connect();
    $inventory = new Inventory($db);
    $user = new User($db);

    if (!$inventory->getById($id)) {
        throw new Exception('Инвентаризация не найдена');
    }

    // Получаем данные создателя
    $creator = $user->getById($inventory->created_by_user_id);

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<div class="content-header">
    <h1 class="content-title">Просмотр инвентаризации</h1>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <?php if (get_current_user_role() === 'admin'): ?>
            <a href="edit.php?id=<?= $id ?>" class="btn btn-primary ms-2">
                <i class="bi bi-pencil"></i> Редактировать
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2 fw-bold">Название:</div>
            <div class="col-md-10"><?= htmlspecialchars($inventory->name) ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2 fw-bold">Статус:</div>
            <div class="col-md-10">
                <span class="badge bg-<?= 
                    $inventory->status === 'completed' ? 'success' : 
                    ($inventory->status === 'in_progress' ? 'warning' : 'secondary')
                ?>">
                    <?= $inventory->status === 'completed' ? 'Завершена' : 
                        ($inventory->status === 'in_progress' ? 'В процессе' : 'Запланирована') ?>
                </span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2 fw-bold">Дата начала:</div>
            <div class="col-md-10"><?= date('d.m.Y', strtotime($inventory->start_date)) ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2 fw-bold">Дата окончания:</div>
            <div class="col-md-10"><?= $inventory->end_date ? date('d.m.Y', strtotime($inventory->end_date)) : '-' ?></div>
        </div>
    </div>
</div>

<!-- Форма для изменения статуса -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-arrow-repeat"></i> Изменить статус инвентаризации
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="change_status" value="1">
            
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <select name="status" class="form-select" required>
                        <option value="planned" <?= $inventory->status === 'planned' ? 'selected' : '' ?>>Запланирована</option>
                        <option value="in_progress" <?= $inventory->status === 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                        <option value="completed" <?= $inventory->status === 'completed' ? 'selected' : '' ?>>Завершена</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Обновить статус
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
[file content end]