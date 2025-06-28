<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../models/Consumable.php';
require_once '../../models/ConsumableCharacteristic.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);
$characteristic = new ConsumableCharacteristic($db);

$item = $consumable->getById($_GET['id']);
if (!$item) {
    header("Location: index.php");
    exit();
}

$characteristics = $characteristic->getByConsumable($_GET['id']);

$page_title = "Просмотр расходника: " . htmlspecialchars($item['name']);
?>

<div class="content-header">
    <h1><?= $page_title ?></h1>
    <a href="index.php" class="btn btn-secondary">Назад</a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Основная информация
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Название:</label>
                    <div class="form-control-plaintext"><?= htmlspecialchars($item['name']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Тип:</label>
                    <div class="form-control-plaintext"><?= htmlspecialchars($item['type_name'] ?? 'Не указан') ?></div>
                </div>
                

                <div class="mb-3">
                    <label class="form-label">Дата поступления:</label>
                    <div class="form-control-plaintext"><?= $item['receipt_date'] ? htmlspecialchars($item['receipt_date']) : 'Не указана' ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Для оборудования:</label>
                    <div class="form-control-plaintext"><?= htmlspecialchars($item['equipment_name'] ?? 'Не указано') ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ответственный:</label>
                    <div class="form-control-plaintext"><?= htmlspecialchars($item['responsible_name'] ?? 'Не указан') ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Описание:</label>
                    <div class="form-control-plaintext"><?= $item['description'] ? nl2br(htmlspecialchars($item['description'])) : 'Нет описания' ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><i class="bi bi-list-check"></i> Характеристики</div>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCharacteristicModal">
                    <i class="bi bi-plus"></i> Добавить
                </button>
            </div>
            <div class="card-body">
                <?php if($characteristics->rowCount() > 0): ?>
                    <div class="list-group">
                        <?php while($char = $characteristics->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($char['name']) ?>:</strong> 
                                <?= htmlspecialchars($char['value']) ?>
                            </div>
                            <a href="delete_characteristic.php?id=<?= $char['id'] ?>&consumable_id=<?= $item['id'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Удалить характеристику?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">Характеристики не добавлены</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления характеристики -->
<div class="modal fade" id="addCharacteristicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить характеристику</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="add_characteristic.php">
                <div class="modal-body">
                    <input type="hidden" name="consumable_id" value="<?= $item['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Название характеристики</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Значение</label>
                        <input type="text" class="form-control" name="value" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>