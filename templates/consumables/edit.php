<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../models/Consumable.php';
require_once '../../models/ReferenceItem.php';
require_once '../../models/Equipment.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);
$reference = new ReferenceItem($db);
$equipment = new Equipment($db);

$item = $consumable->getById($_GET['id']);
if (!$item) {
    header("Location: index.php");
    exit();
}

$types = $reference->getByType('consumable_type')->fetchAll(PDO::FETCH_ASSOC);
$equipments = $equipment->getAll()->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Редактировать расходный материал";
?>

<div class="content-header">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <a href="index.php" class="btn btn-secondary">Назад</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="edit_handler.php" method="POST">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            
            <div class="mb-3">
                <label class="form-label">Название *</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Тип</label>
                <select class="form-select" name="type_id">
                    <option value="">Выберите тип</option>
                    <?php foreach($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= $item['type_id'] == $type['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Для оборудования</label>
                <select class="form-select" name="equipment_id">
                    <option value="">Не привязано</option>
                    <?php foreach($equipments as $eq): ?>
                        <option value="<?= $eq['id'] ?>" <?= $item['equipment_id'] == $eq['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Дата поступления</label>
                <input type="date" class="form-control" name="receipt_date" 
                       value="<?= htmlspecialchars($item['receipt_date']) ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Описание</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>