<?php
require_once 'includes/header.php';
$page_title = "Вход в систему";
require_once 'config/database.php';
require_once 'models/User.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$db = (new Database())->connect();
$user = new User($db);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Валидация
    if (empty($username) || empty($password)) {
        $error = "Имя пользователя и пароль обязательны для заполнения";
    } else {
        // Попытка аутентификации
        if ($user->login($username, $password)) {
            // Получаем полные данные о пользователе
            if ($user->getById($user->id)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['username'] = $user->username;

                // Сохраняем полное имя пользователя для отображения
                $fullName = trim($user->last_name . ' ' . $user->first_name);
                $_SESSION['user_full_name'] = !empty($fullName) ? $fullName : $user->username;

                // Перенаправление на запрошенную страницу или на главную
                $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);

                // Удаляем /UP/ из пути, если он есть
                $redirect_url = preg_replace('/^\/UP\//', '', $redirect_url);

                header("Location: $redirect_url");
                exit();
            } else {
                $error = "Ошибка получения данных пользователя";
            }
        } else {
            $error = "Неверное имя пользователя или пароль";
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Вход в систему</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">Для входа используйте ваши учетные данные</small>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>

