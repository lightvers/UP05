<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Inventory.php';

require_login();
require_admin(); // Только для администраторов

$page_title = "Создание инвентаризации";

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = (new Database())->connect();
        $inventory = new Inventory($db);

        $form_data = [
            'name' => trim($_POST['name'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? '')
        ];

        // Валидация
        if (empty($form_data['name'])) {
            $errors['name'] = 'Введите название';
        }
        if (empty($form_data['start_date'])) {
            $errors['start_date'] = 'Укажите дату начала';
        }
        if (empty($form_data['end_date'])) {
            $errors['end_date'] = 'Укажите дату окончания';
        }
        
        // Проверка дат
        if (!empty($form_data['start_date']) && !empty($form_data['end_date'])) {
            $start = new DateTime($form_data['start_date']);
            $end = new DateTime($form_data['end_date']);
            
            if ($end < $start) {
                $errors['end_date'] = 'Дата окончания должна быть позже даты начала';
            }
        }

        if (empty($errors)) {
            $inventory->name = $form_data['name'];
            $inventory->start_date = $form_data['start_date'];
            $inventory->end_date = $form_data['end_date'];
            $inventory->created_by_user_id = $_SESSION['user_id'];
            $inventory->status = 'planned';

            if ($inventory->create()) {
                $_SESSION['success'] = 'Инвентаризация успешно создана';
                header('Location: index.php');
                exit;
            } else {
                throw new Exception('Ошибка при создании инвентаризации');
            }
        }

    } catch (Exception $e) {
        $errors['general'] = $e->getMessage();
    }
}
?>

<div class="content-header">
    <h1 class="content-title">Создание инвентаризации</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" id="inventoryForm">
            <div class="mb-3">
                <label for="name" class="form-label">Название *</label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                       id="name" name="name" value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Дата начала *</label>
                        <input type="date" class="form-control <?= isset($errors['start_date']) ? 'is-invalid' : '' ?>" 
                               id="start_date" name="start_date" value="<?= htmlspecialchars($form_data['start_date'] ?? '') ?>" required>
                        <?php if (isset($errors['start_date'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['start_date']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Дата окончания *</label>
                        <input type="date" class="form-control <?= isset($errors['end_date']) ? 'is-invalid' : '' ?>" 
                               id="end_date" name="end_date" value="<?= htmlspecialchars($form_data['end_date'] ?? '') ?>" required>
                        <?php if (isset($errors['end_date'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['end_date']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Создать</button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>

<script>
// Аналогичный скрипт валидации как в edit.php
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('inventoryForm');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    [startDateInput, endDateInput].forEach(input => {
        input.addEventListener('change', validateDates);
    });

    form.addEventListener('submit', function(e) {
        if (!validateDates() || !validateRequired()) {
            e.preventDefault();
        }
    });

    function validateRequired() {
        let valid = true;
        ['name', 'start_date', 'end_date'].forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                valid = false;
            }
        });
        return valid;
    }

    function validateDates() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (startDate && endDate && endDate < startDate) {
            endDateInput.classList.add('is-invalid');
            let feedback = endDateInput.nextElementSibling;
            
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                endDateInput.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = 'Дата окончания должна быть позже даты начала';
            return false;
        } else {
            endDateInput.classList.remove('is-invalid');
            return true;
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>