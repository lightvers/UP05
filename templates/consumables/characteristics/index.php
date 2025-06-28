<?php
require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';
require_login();

if (!isset($_GET['consumable_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../../../models/Consumable.php';
require_once '../../../models/ConsumableCharacteristic.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);
$characteristic = new ConsumableCharacteristic($db);

$consumable_id = $_GET['consumable_id'];
$item = $consumable->getById($consumable_id);
if (!$item) {
    header("Location: ../index.php");
    exit();
}

$characteristics = $characteristic->getByConsumable($consumable_id);

$page_title = "Характеристики расходника: " . htmlspecialchars($item['name']);
?>

<div class="content-header">
    <h1><?= $page_title ?></h1>
    <div>
        <a href="../index.php" class="btn btn-secondary">Назад</a>
        <a href="create.php?consumable_id=<?= $consumable_id ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить характеристику
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if($characteristics->rowCount() > 0): ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Значение</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($char = $characteristics->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($char['name']) ?></td>
                        <td><?= htmlspecialchars($char['value']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $char['id'] ?>&consumable_id=<?= $consumable_id ?>" 
                               class="btn btn-sm btn-outline-secondary">Редактировать</a>
                            <a href="delete.php?id=<?= $char['id'] ?>&consumable_id=<?= $consumable_id ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Удалить характеристику?')">Удалить</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">Характеристики не добавлены</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>