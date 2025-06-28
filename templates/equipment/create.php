<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

require_once '../../config/database.php';
require_once '../../models/Equipment.php';
require_once '../../models/ReferenceItem.php';
require_once '../../models/Classroom.php';
require_once '../../models/User.php';

$db = (new Database())->connect();

// Загрузка данных для выпадающих списков
$statuses = (new ReferenceItem($db))->getByType('status');
$classrooms = (new Classroom($db))->getAll();
$users = (new User($db))->getAll();
$models = (new Equipment($db))->getAll();
$directions = (new ReferenceItem($db))->getByType('direction');

$page_title = "Добавить оборудование";
?>

<div class="content-header">
    <h1 class="content-title"><?= $page_title ?></h1>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Назад</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="create_handler.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Наименование *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inventory_number" class="form-label">Инвентарный номер *</label>
                        <input type="text" class="form-control" id="inventory_number" name="inventory_number" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="model_id" class="form-label">Модель оборудования</label>
                        <select class="form-select" id="model_id" name="model_id">
                            <option value="">Не выбрано</option>
                            <?php while($model = $models->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $model['id'] ?>"><?= htmlspecialchars($model['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direction_id" class="form-label">Направление</label>
                        <select class="form-select" id="direction_id" name="direction_id">
                            <option value="">Не выбрано</option>
                            <?php while($direction = $directions->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $direction['id'] ?>"><?= htmlspecialchars($direction['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Статус *</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <option value="">Выберите статус</option>
                            <?php while($status = $statuses->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cost" class="form-label">Стоимость</label>
                        <input type="number" step="0.01" class="form-control" id="cost" name="cost">
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Фото оборудования</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- Блок выбора аудитории -->
            <div class="mb-3">
                <label class="form-label">Аудитория</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="classroomDisplay" placeholder="Не выбрана" readonly>
                    <input type="hidden" name="current_classroom_id" id="classroomId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#classroomModal">
                        <i class="bi bi-search"></i> Выбрать
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearSelection('classroom')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Блок выбора ответственного -->
            <div class="mb-3">
                <label class="form-label">Мат. ответственное лицо</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="responsibleDisplay" placeholder="Не выбрано" readonly>
                    <input type="hidden" name="responsible_user_id" id="responsibleId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" 
                            data-bs-target="#userModal" data-user-type="responsible">
                        <i class="bi bi-search"></i> Выбрать
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearSelection('responsible')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Блок выбора временного ответственного -->
            <div class="mb-3">
                <label class="form-label">Временный ответственный</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="tempResponsibleDisplay" placeholder="Не выбрано" readonly>
                    <input type="hidden" name="temp_responsible_user_id" id="tempResponsibleId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" 
                            data-bs-target="#userModal" data-user-type="tempResponsible">
                        <i class="bi bi-search"></i> Выбрать
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearSelection('tempResponsible')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="comments" class="form-label">Комментарии</label>
                <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Сохранить</button>
        </form>
    </div>
</div>

<!-- Модальное окно выбора аудитории -->
<div class="modal fade" id="classroomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор аудитории</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="classroomSearch" placeholder="Поиск..." oninput="filterItems('classroomResults', this.value)">
                <div class="list-group" id="classroomResults" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $classrooms->execute();
                    while($room = $classrooms->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                        <a href="#" class="list-group-item list-group-item-action classroom-item" 
                           data-id="<?= $room['id'] ?>" 
                           data-name="<?= htmlspecialchars($room['name']) ?>">
                            <?= htmlspecialchars($room['name']) ?>
                            <small class="text-muted float-end"><?= $room['short_name'] ?? '' ?></small>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно выбора пользователя -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="userSearch" placeholder="Поиск..." oninput="filterItems('userResults', this.value)">
                <div class="list-group" id="userResults" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $users->execute();
                    while($user = $users->fetch(PDO::FETCH_ASSOC)):
                        $fullName = trim($user['last_name'] . ' ' . $user['first_name'] . ' ' . ($user['middle_name'] ?? ''));
                    ?>
                        <a href="#" class="list-group-item list-group-item-action user-item" 
                           data-id="<?= $user['id'] ?>" 
                           data-name="<?= htmlspecialchars($fullName) ?>">
                            <?= htmlspecialchars($fullName) ?>
                            <small class="text-muted float-end"><?= $user['role'] ?? '' ?></small>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обработчики для выбора аудитории
    document.querySelectorAll('.classroom-item').forEach(item => {
        item.addEventListener('click', function() {
            selectItem(this, 'classroom');
        });
    });

    // Обработчики для выбора пользователя
    document.querySelectorAll('.user-item').forEach(item => {
        item.addEventListener('click', function() {
            const userType = document.getElementById('userModal').dataset.userType;
            selectItem(this, userType);
        });
    });

    // Инициализация типа пользователя для модального окна
    document.getElementById('userModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        this.dataset.userType = button.dataset.userType;
    });
});

// Функция выбора элемента
function selectItem(element, type) {
    const id = element.dataset.id;
    const name = element.dataset.name;
    
    if (type === 'classroom') {
        document.getElementById('classroomId').value = id;
        document.getElementById('classroomDisplay').value = name;
        bootstrap.Modal.getInstance(document.getElementById('classroomModal')).hide();
    } else {
        document.getElementById(`${type}Id`).value = id;
        document.getElementById(`${type}Display`).value = name;
        bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
    }
}

// Функция фильтрации элементов
function filterItems(listId, searchText) {
    const items = document.querySelectorAll(`#${listId} .list-group-item`);
    searchText = searchText.toLowerCase();
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchText) ? 'block' : 'none';
    });
}

// Очистка выбора
function clearSelection(type) {
    document.getElementById(`${type}Id`).value = '';
    document.getElementById(`${type}Display`).value = '';
}
</script>

<?php require_once '../../includes/footer.php'; ?>