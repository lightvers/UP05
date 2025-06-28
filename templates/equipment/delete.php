<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Не указан ID оборудования";
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

require_once '../../config/database.php';
require_once '../../models/Equipment.php';

$db = (new Database())->connect();
$equipment = new Equipment($db);

$item = $equipment->getById($id);
if (!$item) {
    $_SESSION['error'] = "Оборудование не найдено";
    header("Location: index.php");
    exit();
}

$page_title = "Удаление оборудования";
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="delete_handler.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <p>Вы действительно хотите удалить следующее оборудование?</p>
            
            <div class="mb-3">
                <label class="form-label">Наименование</label>
                <div class="form-control-static"><?= htmlspecialchars($item['name']) ?></div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Инвентарный номер</label>
                <div class="form-control-static"><?= htmlspecialchars($item['inventory_number']) ?></div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Аудитория</label>
                <div class="form-control-static"><?= htmlspecialchars($item['classroom_name'] ?? 'Не указана') ?></div>
            </div>
            
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Удалить
            </button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>