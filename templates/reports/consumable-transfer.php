<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_admin();

$page_title = "Акт приема-передачи расходных материалов";
require_once '../../models/Consumable.php';
require_once '../../models/User.php';
require_once '../../models/Database.php';
require_once '../../vendor/autoload.php';

// Инициализация подключения к базе данных
try {
    $db = (new Database())->connect();
    $consumable = new Consumable($db);
    $user = new User($db);
    
    // Получаем списки для формы
    $consumables_list = $consumable->getAll();
    $recipients_list = $user->getAll();
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$error = null;

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Проверка CSRF токена
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Недействительный CSRF токен");
        }

        // Валидация данных
        $required_fields = ['consumable_id', 'quantity', 'recipient_id'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Не заполнено обязательное поле: " . $field);
            }
        }

        $consumable_id = (int)$_POST['consumable_id'];
        $quantity = (int)$_POST['quantity'];
        $recipient_id = (int)$_POST['recipient_id'];
        $comments = trim($_POST['comments'] ?? '');
        
        // Проверка данных
        if ($consumable_id <= 0 || $quantity <= 0 || $recipient_id <= 0) {
            throw new Exception("Неверные данные формы");
        }
        
        // Получаем данные расходника
        $current_consumable = $consumable->getById($consumable_id);
        if (!$current_consumable) {
            throw new Exception("Расходный материал не найден");
        }
        
        if ($quantity > $current_consumable['quantity']) {
            throw new Exception("Недостаточное количество на складе (доступно: {$current_consumable['quantity']})");
        }
        
        // Получаем данные получателя
        $recipient = $user->getById($recipient_id);
        if (!$recipient) {
            throw new Exception("Получатель не найден");
        }
        
        // Создаем PDF документ
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('КГАПОУ Пермский Авиационный техникум');
        $pdf->SetTitle('Акт приема-передачи расходных материалов');
        $pdf->SetSubject('Передача материалов');
        
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('dejavusans', '', 10);
        
        $pdf->AddPage();
        
        // HTML-контент для PDF
        $html = '<h1 style="text-align:center;">Акт приема-передачи расходных материалов</h1>';
        $html .= '<p style="text-align:right;">г. Пермь, '.date('d.m.Y').'</p>';
        $html .= '<p>КГАПОУ Пермский Авиационный техникум им. А.Д. Швецова передает, а сотрудник ';
        $html .= htmlspecialchars($recipient['last_name']).' '.htmlspecialchars($recipient['first_name']).' ';
        $html .= htmlspecialchars($recipient['middle_name']).' принимает следующие расходные материалы:</p>';
        
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color:#f2f2f2;">';
        $html .= '<th width="40%">Наименование</th>';
        $html .= '<th width="20%">Тип</th>';
        $html .= '<th width="15%">Количество</th>';
        $html .= '<th width="25%">Дата поступления</th>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td>'.htmlspecialchars($current_consumable['name']).'</td>';
        $html .= '<td>'.htmlspecialchars($current_consumable['type_name'] ?? '-').'</td>';
        $html .= '<td>'.$quantity.'</td>';
        $html .= '<td>'.($current_consumable['receipt_date'] ? date('d.m.Y', strtotime($current_consumable['receipt_date'])) : '-').'</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        $html .= '<p style="margin-top:20px;">';
        $html .= '<strong>Передал:</strong> ___________________ (подпись)<br>';
        $html .= '<strong>Принял:</strong> ___________________ (подпись)';
        $html .= '</p>';
        
        if (!empty($comments)) {
            $html .= '<p style="font-style:italic;margin-top:15px;">Примечание: '.htmlspecialchars($comments).'</p>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Обновляем количество расходников в базе
        $new_quantity = $current_consumable['quantity'] - $quantity;
        if (!$consumable->updateQuantity($consumable_id, $new_quantity)) {
            throw new Exception("Ошибка при обновлении количества расходников");
        }
        
        // Логируем передачу
        $transfer_data = [
            'consumable_id' => $consumable_id,
            'quantity' => $quantity,
            'recipient_id' => $recipient_id,
            'transfer_date' => date('Y-m-d H:i:s'),
            'comments' => $comments
        ];
        
        $stmt = $db->prepare("INSERT INTO transfers (consumable_id, quantity, recipient_id, transfer_date, comments) 
                             VALUES (:consumable_id, :quantity, :recipient_id, :transfer_date, :comments)");
        if (!$stmt->execute($transfer_data)) {
            throw new Exception("Ошибка при записи данных о передаче");
        }
        
        // Вывод PDF
        $pdf->Output('Акт приема-передачи '.date('Y-m-d').'.pdf', 'I');
        exit();
        
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        $error = "Произошла ошибка при формировании акта: " . $e->getMessage();
    }
}
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
    <div class="content-actions">
        <a href="../reports/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к отчетам
        </a>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-text"></i> Форма создания акта
            </div>
            <div class="card-body">
                <form method="POST" id="transferForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="consumable_id" class="form-label required-field">Расходный материал</label>
                            <select class="form-select" id="consumable_id" name="consumable_id" required>
                                <option value="">-- Выберите расходный материал --</option>
                                <?php foreach ($consumables_list as $row): ?>
                                    <option value="<?= $row['id'] ?>" <?= isset($_POST['consumable_id']) && $_POST['consumable_id'] == $row['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['name']) ?> (<?= $row['quantity'] ?> шт.)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="quantity" class="form-label required-field">Количество</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required 
                                   value="<?= $_POST['quantity'] ?? 1 ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="recipient_id" class="form-label required-field">Получатель</label>
                            <select class="form-select" id="recipient_id" name="recipient_id" required>
                                <option value="">-- Выберите получателя --</option>
                                <?php foreach ($recipients_list as $row): ?>
                                    <option value="<?= $row['id'] ?>" <?= isset($_POST['recipient_id']) && $_POST['recipient_id'] == $row['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['last_name']) ?> <?= htmlspecialchars($row['first_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="transfer_date" class="form-label">Дата передачи</label>
                            <input type="date" class="form-control" id="transfer_date" name="transfer_date" 
                                   value="<?= date('Y-m-d') ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label">Комментарий</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3"><?= $_POST['comments'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> Сформировать акт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Информация
            </div>
            <div class="card-body">
                <h5>Как использовать:</h5>
                <ol>
                    <li>Выберите расходный материал из списка</li>
                    <li>Укажите количество для передачи</li>
                    <li>Выберите получателя из списка сотрудников</li>
                    <li>При необходимости добавьте комментарий</li>
                    <li>Нажмите "Сформировать акт"</li>
                </ol>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-exclamation-circle"></i> После формирования акта количество материала на складе будет автоматически уменьшено.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('transferForm').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Формирование...';
});
</script>

<?php require_once '../../includes/footer.php'; ?>