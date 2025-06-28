<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_once '../../models/ReferenceItem.php';
require_once '../../config/database.php';

require_admin();

$page_title = "Модели оборудования";
$reference_type = "equipment_model";

$db = (new Database())->connect();
$reference = new ReferenceItem($db);

$items = $reference->getByType($reference_type);
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus"></i> Добавить модель
    </button>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list"></i> Список моделей</span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="alertContainer"></div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items && $items->rowCount() > 0): ?>
                        <?php while($item = $items->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-btn"
                                                data-id="<?= $item['id'] ?>"
                                                data-name="<?= htmlspecialchars($item['name']) ?>"
                                                data-description="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-btn"
                                                data-id="<?= $item['id'] ?>"
                                                data-name="<?= htmlspecialchars($item['name']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Нет данных</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модальные окна (аналогичные consumable-types.php) -->
<?php include 'reference_modals.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация модальных окон
    const editModal = new bootstrap.Modal('#editModal');
    const deleteModal = new bootstrap.Modal('#deleteModal');
    
    // Обработчики кнопок редактирования
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editId').value = this.dataset.id;
            document.getElementById('editName').value = this.dataset.name;
            document.getElementById('editDescription').value = this.dataset.description;
            editModal.show();
        });
    });
    
    // Обработчики кнопок удаления
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('deleteId').value = this.dataset.id;
            document.getElementById('deleteItemName').textContent = this.dataset.name;
            deleteModal.show();
        });
    });
    
    // Обработка формы создания
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await handleForm(this, 'create');
    });
    
    // Обработка формы редактирования
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await handleForm(this, 'update');
    });
    
    // Обработка формы удаления
    document.getElementById('deleteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await handleForm(this, 'delete');
    });
    
    async function handleForm(form, action) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Обработка...';
        
        try {
            const response = await fetch('reference_handler.php?action=' + action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                showAlert(data.message, 'danger');
                
                // Показ ошибок валидации
                if (action !== 'delete' && data.errors) {
                    for (const [field, error] of Object.entries(data.errors)) {
                        const input = form.querySelector(`[name="${field}"]`);
                        const feedback = input.nextElementSibling;
                        
                        if (input && feedback) {
                            input.classList.add('is-invalid');
                            feedback.textContent = error;
                        }
                    }
                }
            }
        } catch (error) {
            showAlert('Ошибка сети', 'danger');
            console.error(error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
    
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>