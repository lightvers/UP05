<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';

require_login();

$page_title = "Удаление инвентаризации";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = (new Database())->connect();
    $inventory = new Inventory($db);

    if (!$inventory->getById($id)) {
        throw new Exception('Инвентаризация не найдена');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($inventory->delete()) {
            $_SESSION['success'] = 'Инвентаризация успешно удалена';
            header('Location: index.php');
            exit;
        } else {
            throw new Exception('Ошибка при удалении инвентаризации');
        }
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<div class="content-header">
    <h1 class="content-title">Удаление инвентаризации</h1>
    <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> Вы действительно хотите удалить инвентаризацию "<?= htmlspecialchars($inventory->name) ?>"?
        </div>

        <form method="POST">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Удалить
            </button>
            <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">
                Отмена
            </a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>