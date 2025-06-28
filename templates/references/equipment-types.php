<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_once '../../models/ReferenceItem.php';
require_once '../../config/database.php';

// Проверяем права администратора
require_admin();
$page_title = "Типы оборудования";
$reference_type = "equipment_type"; // Тип справочника

// Подключение к базе данных
$db = (new Database())->connect();
$reference = new ReferenceItem($db);

// Получение всех типов оборудования
$items = $reference->getByType($reference_type);
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus"></i> Добавить тип
    </button>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list"></i> Список типов оборудования</span>
            <a href="/UP/templates/references/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> К списку справочников
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Уведомления -->
        <div id="alertContainer">
            <?php if(isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?= $_SESSION['alert']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['alert']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>
        </div>

        <!-- Таблица типов -->
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
                                        <button type="button" class="btn btn-outline-primary edit-btn"
                                                data-id="<?= $item['id'] ?>"
                                                data-name="<?= htmlspecialchars($item['name']) ?>"
                                                data-description="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger delete-btn"
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
                            <td colspan="4" class="text-center">Типы оборудования не найдены</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модальное окно для создания типа -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Добавить тип оборудования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm">
                <div class="modal-body">
                    <input type="hidden" name="type" value="<?= $reference_type ?>">

                    <div class="mb-3">
                        <label for="createName" class="form-label">Название *</label>
                        <input type="text" class="form-control" id="createName" name="name" required>
                        <div class="invalid-feedback" id="createNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="createDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="createDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования типа -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Редактировать тип оборудования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="type" value="<?= $reference_type ?>">

                    <div class="mb-3">
                        <label for="editName" class="form-label">Название *</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                        <div class="invalid-feedback" id="editNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите удалить тип оборудования <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger">Это действие нельзя отменить. Удаление типа может повлиять на связанное оборудование.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteForm">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Инициализация модальных окон редактирования
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');

        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description;

        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    });
});

// Инициализация модальных окон удаления
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');

        document.getElementById('deleteId').value = id;
        document.getElementById('deleteItemName').textContent = name;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
});

// Обработка формы создания
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Сброс ошибок
    resetValidation('create');

    // Получаем элементы формы
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Показываем индикатор загрузки
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Сохранение...';

    try {
        // Отправляем данные на сервер
        const formData = new FormData(form);
        const response = await fetch('/UP/templates/references/reference_handler.php?action=create', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Успешное создание - перезагрузим страницу
            window.location.reload();
        } else {
            // Обработка ошибок
            if (data.errors) {
                Object.entries(data.errors).forEach(([field, message]) => {
                    const input = document.getElementById('create' + field.charAt(0).toUpperCase() + field.slice(1));
                    const errorElement = document.getElementById('create' + field.charAt(0).toUpperCase() + field.slice(1) + 'Error');

                    if (input && errorElement) {
                        input.classList.add('is-invalid');
                        errorElement.textContent = message;
                    }
                });
            }

            showAlert('modal', 'createModal', data.message || 'Произошла ошибка', 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showAlert('modal', 'createModal', 'Произошла ошибка при отправке формы', 'danger');
    } finally {
        // Восстанавливаем кнопку
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

// Обработка формы редактирования
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Сброс ошибок
    resetValidation('edit');

    // Получаем элементы формы
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Показываем индикатор загрузки
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Сохранение...';

    try {
        // Отправляем данные на сервер
        const formData = new FormData(form);
        const response = await fetch('/UP/templates/references/reference_handler.php?action=update', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Успешное обновление - перезагрузим страницу
            window.location.reload();
        } else {
            // Обработка ошибок
            if (data.errors) {
                Object.entries(data.errors).forEach(([field, message]) => {
                    const input = document.getElementById('edit' + field.charAt(0).toUpperCase() + field.slice(1));
                    const errorElement = document.getElementById('edit' + field.charAt(0).toUpperCase() + field.slice(1) + 'Error');

                    if (input && errorElement) {
                        input.classList.add('is-invalid');
                        errorElement.textContent = message;
                    }
                });
            }

            showAlert('modal', 'editModal', data.message || 'Произошла ошибка', 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showAlert('modal', 'editModal', 'Произошла ошибка при отправке формы', 'danger');
    } finally {
        // Восстанавливаем кнопку
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

// Обработка формы удаления
document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Получаем элементы формы
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Показываем индикатор загрузки
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Удаление...';

    try {
        // Отправляем данные на сервер
        const id = document.getElementById('deleteId').value;
        const response = await fetch(`/UP/templates/references/reference_handler.php?action=delete&id=${id}`, {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            // Успешное удаление - перезагрузим страницу
            window.location.reload();
        } else {
            showAlert('modal', 'deleteModal', data.message || 'Произошла ошибка при удалении', 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showAlert('modal', 'deleteModal', 'Произошла ошибка при отправке формы', 'danger');
    } finally {
        // Восстанавливаем кнопку
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

function resetValidation(prefix) {
    document.querySelectorAll(`.is-invalid`).forEach(el => {
        el.classList.remove('is-invalid');
    });

    document.querySelectorAll(`.invalid-feedback`).forEach(el => {
        el.textContent = '';
    });
}

function showAlert(type, modalId, message, alertType = 'success') {
    if (type === 'modal') {
        // Показываем уведомление внутри модального окна
        const modalBody = document.querySelector(`#${modalId} .modal-body`);
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${alertType} mt-3`;
        alertElement.textContent = message;

        // Удаляем предыдущие уведомления, если они есть
        const existingAlerts = modalBody.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        modalBody.appendChild(alertElement);
    } else {
        // Показываем уведомление на странице
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = `
            <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
