<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_admin();

$page_title = "Удаление аудитории";

require_once '../../models/Classroom.php';
require_once '../../models/Equipment.php';

$db = (new Database())->connect();
$classroom = new Classroom($db);
$equipment = new Equipment($db);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$classroom->getById($id)) {
    header("Location: index.php");
    exit();
}

$equipment_count = $equipment->getCountByClassroom($id);

// Обработка формы удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перемещаем оборудование (если есть)
    if ($equipment_count > 0) {
        $equipment->moveAllFromClassroom($id, null);
    }
    
    // Удаляем аудиторию
    if ($classroom->delete($id)) {
        $_SESSION['success_message'] = 'Аудитория успешно удалена';
        header("Location: index.php");
        exit();
    } else {
        $error = 'Не удалось удалить аудиторию';
    }
}
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
    <a href="view.php?id=<?= $id ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card border-danger">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle"></i> Подтверждение удаления
    </div>
    <div class="card-body">
        <?php if ($equipment_count > 0): ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-exclamation-triangle-fill"></i> Внимание!</h5>
                <p>В этой аудитории находится <?= $equipment_count ?> единиц оборудования.</p>
                <p>При удалении все оборудование будет перемещено в категорию "Не назначено".</p>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-danger">
            <h5><i class="bi bi-trash"></i> Вы уверены, что хотите удалить эту аудиторию?</h5>
            <p>Это действие нельзя отменить!</p>
        </div>
        
        <form method="post" class="mt-4">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="d-flex justify-content-between">
                <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Отмена
                </a>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Подтвердить удаление
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>