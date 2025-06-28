<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Classroom.php';
require_once '../../models/User.php';

// Проверка прав администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = 'Доступ запрещен';
    header("Location: index.php");
    exit();
}

$page_title = "Редактирование аудитории";

try {
    // Инициализация подключения
    $database = new Database();
    $db = $database->connect();
    
    $classroom = new Classroom($db);
    $user = new User($db);

    // Получаем ID аудитории
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        throw new Exception('Не указан ID аудитории');
    }

    // Получаем данные аудитории с проверкой
    $classroom_data = $classroom->getById($id);
    if (!$classroom_data) {
        throw new Exception('Аудитория не найдена');
    }

    // Получаем список пользователей с проверкой
    $users_stmt = $user->getAll();
    if (!$users_stmt) {
        throw new Exception('Ошибка при загрузке пользователей');
    }
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    $users = is_array($users) ? $users : []; // Гарантируем, что это массив

    // Получаем данные ответственных с проверкой
    $responsible_data = ['last_name' => '', 'first_name' => 'Не указан'];
    if (!empty($classroom->responsible_user_id)) {
        $resp_data = $user->getById($classroom->responsible_user_id);
        if ($resp_data && is_array($resp_data)) {
            $responsible_data = $resp_data;
        }
    }

    $temp_responsible_data = ['last_name' => '', 'first_name' => 'Не указан'];
    if (!empty($classroom->temp_responsible_user_id)) {
        $temp_data = $user->getById($classroom->temp_responsible_user_id);
        if ($temp_data && is_array($temp_data)) {
            $temp_responsible_data = $temp_data;
        }
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="/UP/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Встроенный sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-brand">
                    <span>Учет оборудования</span>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="/UP/index.php" class="sidebar-nav-link">
                            <i class="bi bi-house-door sidebar-nav-icon"></i> Главная
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="/UP/templates/equipment/index.php" class="sidebar-nav-link">
                            <i class="bi bi-pc-display sidebar-nav-icon"></i> Оборудование
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="/UP/templates/classrooms/index.php" class="sidebar-nav-link active">
                            <i class="bi bi-building sidebar-nav-icon"></i> Аудитории
                        </a>
                    </li>
                </ul>
            </div>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger mt-3"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="content-header">
                    <h1><?= htmlspecialchars($page_title) ?></h1>
                    <a href="index.php" class="btn btn-secondary">Назад</a>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <form action="edit_handler.php" method="POST">
                            <input type="hidden" name="id" value="<?= $id ?>">

                            <div class="mb-3">
                                <label class="form-label">Название аудитории *</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= htmlspecialchars($classroom->name ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Краткое название</label>
                                <input type="text" class="form-control" name="short_name"
                                       value="<?= htmlspecialchars($classroom->short_name ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ответственный</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="responsibleDisplay" 
                                           value="<?= htmlspecialchars($responsible_data['last_name'] . ' ' . $responsible_data['first_name']) ?>" 
                                           readonly>
                                    <input type="hidden" name="responsible_user_id" id="responsible_user_id" 
                                           value="<?= $classroom->responsible_user_id ?>">
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
                                    <input type="text" class="form-control" id="tempResponsibleDisplay" 
                                           value="<?= htmlspecialchars($temp_responsible_data['last_name'] . ' ' . $temp_responsible_data['first_name']) ?>" 
                                           readonly>
                                    <input type="hidden" name="temp_responsible_user_id" id="temp_responsible_user_id" 
                                           value="<?= $classroom->temp_responsible_user_id ?>">
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

                <!-- Модальное окно выбора пользователя -->
                <div class="modal fade" id="userModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Выбор пользователя</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="userSearch" placeholder="Поиск..." oninput="filterUsers()">
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
                                            <tr class="user-row">
                                                <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary select-user"
                                                            data-id="<?= $user['id'] ?? '' ?>"
                                                            data-name="<?= htmlspecialchars(($user['last_name'] ?? '') . ' ' . ($user['first_name'] ?? '')) ?>">
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
                document.addEventListener('DOMContentLoaded', function() {
                    let currentField = '';
                    
                    // При открытии модального окна
                    document.getElementById('userModal').addEventListener('show.bs.modal', function(e) {
                        currentField = e.relatedTarget.getAttribute('data-field');
                        document.getElementById('userSearch').value = '';
                        filterUsers();
                    });
                    
                    // Выбор пользователя
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
                });
                
                function filterUsers() {
                    const search = document.getElementById('userSearch').value.toLowerCase();
                    const rows = document.querySelectorAll('.user-row');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(search) ? '' : 'none';
                    });
                }
                
                function clearField(field) {
                    document.getElementById(field + '_user_id').value = '';
                    document.getElementById(field + 'Display').value = '';
                }
                </script>
            </main>
        </div>
    </div>

    <?php require_once '../../includes/footer.php'; ?>
</body>
</html>