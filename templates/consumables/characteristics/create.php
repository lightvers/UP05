<?php
require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';
require_login();

if (!isset($_GET['consumable_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../../../models/Consumable.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);

$consumable_id = $_GET['consumable_id'];
$item = $consumable->getById($consumable_id);
if (!$item) {
    header("Location: ../index.php");
    exit();
}

$page_title = "Добавить характеристику";
?>

<div class="content-header">
    <h1><?= $page_title ?></h1>
    <a href="index.php?consumable_id=<?= $consumable_id ?>" class="btn btn-secondary">Назад</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="create_handler.php" method="POST">
            <input type="hidden" name="consumable_id" value="<?= $consumable_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Название *</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Значение *</label>
                <input type="text" class="form-control" name="value" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>