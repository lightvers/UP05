<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

require_once '../../models/Consumable.php';
require_once '../../models/ReferenceItem.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);
$reference = new ReferenceItem($db);

$search = $_GET['search'] ?? '';
$consumables = $consumable->getAll($search);
$types = $reference->getByType('consumable_type')->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Расходные материалы";
?>

<div class="content-header">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Добавить
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" 
                       placeholder="Поиск по названию или типу..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Поиск</button>
            </div>
        </form>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $consumables->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['type_name'] ?? 'Не указан') ?></td>
                    <td>
                        <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Просмотр</a>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Редактировать</a>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Удалить этот расходник?')">Удалить</a>
                        <a href="characteristics/index.php?consumable_id=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-outline-info">Характеристики</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>