<?php
require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';
require_login();

if (!isset($_GET['id']) || !isset($_GET['consumable_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../../../models/ConsumableCharacteristic.php';
require_once '../../../models/Consumable.php';

$db = (new Database())->connect();
$characteristic = new ConsumableCharacteristic($db);
$consumable = new Consumable($db);

$char_id = $_GET['id'];
$consumable_id = $_GET['consumable_id'];

$item = $characteristic->getById($char_id);
if (!$item) {
    header("Location: index.php?consumable_id=" . $consumable_id);
    exit();
}

$consumable_item = $consumable->getById($consumable_id);

$page_title = "Редактировать характеристику";
?>

<div class="content-header">
    <h1><?= $page_title ?></h1>
    <a href="index.php?consumable_id=<?= $consumable_id ?>" class="btn btn-secondary">Назад</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="edit_handler.php" method="POST">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <input type="hidden" name="consumable_id" value="<?= $consumable_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Название *</label>
                <input type="text" class="form-control" name="name" 
                       value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Значение *</label>
                <input type="text" class="form-control" name="value" 
                       value="<?= htmlspecialchars($item['value']) ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>