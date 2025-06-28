<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

$page_title = "Добавление аудитории";

require_once '../../models/Classroom.php';
require_once '../../models/User.php';

$db = (new Database())->connect();
$classroom = new Classroom($db);
$user = new User($db);

$users = $user->getAll()->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <a href="index.php" class="btn btn-secondary">Назад</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="create_handler.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Название аудитории *</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Краткое название</label>
                <input type="text" class="form-control" name="short_name">
            </div>

            <div class="mb-3">
                <label class="form-label">Ответственный</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="responsibleDisplay" readonly>
                    <input type="hidden" name="responsible_user_id" id="responsible_user_id">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#userModal" data-field="responsible">
                        Выбрать
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearField('responsible')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Временный ответственный</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="tempResponsibleDisplay" readonly>
                    <input type="hidden" name="temp_responsible_user_id" id="temp_responsible_user_id">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#userModal" data-field="temp_responsible">
                        Выбрать
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearField('temp_responsible')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<!-- То же самое модальное окно как в edit.php -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="userSearch" placeholder="Поиск...">
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Фамилия</th>
                                <th>Имя</th>
                                <th>Выбрать</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-user"
                                            data-id="<?= $user['id'] ?>"
                                            data-name="<?= htmlspecialchars($user['last_name'].' '.$user['first_name']) ?>">
                                        Выбрать
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Тот же самый скрипт как в edit.php
document.addEventListener('DOMContentLoaded', function() {
    let currentField = '';
    
    document.getElementById('userModal').addEventListener('show.bs.modal', function(e) {
        currentField = e.relatedTarget.getAttribute('data-field');
        document.getElementById('userSearch').value = '';
        filterUsers();
    });
    
    document.getElementById('userSearch').addEventListener('input', filterUsers);
    
    document.querySelectorAll('.select-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            if (currentField === 'responsible') {
                document.getElementById('responsible_user_id').value = id;
                document.getElementById('responsibleDisplay').value = name;
            } else {
                document.getElementById('temp_responsible_user_id').value = id;
                document.getElementById('tempResponsibleDisplay').value = name;
            }
            
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
        });
    });
    
    function filterUsers() {
        const search = document.getElementById('userSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#userModal tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    }
});

function clearField(field) {
    document.getElementById(field + '_user_id').value = '';
    document.getElementById(field + 'Display').value = '';
}
</script>

<?php require_once '../../includes/footer.php'; ?>