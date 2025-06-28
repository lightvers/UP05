<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_admin();

$database = new Database();
$pdo = $database->connect();

$page_title = "Формирование акта приема-передачи";
$type = $_GET['type'] ?? '';

// Получаем список сотрудников
$employees = [];
try {
    $stmt = $pdo->query("SELECT id, last_name, first_name, middle_name FROM users ORDER BY last_name, first_name");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Ошибка при загрузке списка сотрудников: ' . $e->getMessage() . '</div>';
}

// Получаем список элементов в зависимости от типа акта
$items = [];
try {
    if ($type == 'consumables') {
        $stmt = $pdo->query("SELECT id, name, quantity FROM consumables WHERE quantity > 0 ORDER BY name");
    } else {
        $stmt = $pdo->query("SELECT id, name, inventory_number FROM equipment ORDER BY name");
    }
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Ошибка при загрузке списка оборудования: ' . $e->getMessage() . '</div>';
}

function getActTypeName($type) {
    switch($type) {
        case 'equipment_temporary': return 'Оборудование на временное пользование';
        case 'consumables': return 'Расходные материалы';
        case 'equipment': return 'Оборудование';
        default: return 'Неизвестный тип';
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= htmlspecialchars($page_title) ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Параметры акта</h3>
                    </div>
                    <form id="transferForm" action="transfer-act.php" method="get" onsubmit="return validateForm()">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                        
                        <div class="card-body">
                            <div class="form-group">
                                <label>Тип акта</label>
                                <input type="text" class="form-control" value="<?= getActTypeName($type) ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Ответственный сотрудник</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="employeeDisplay" readonly placeholder="Выберите сотрудника">
                                    <input type="hidden" name="employee_id" id="employeeInput" required>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                                        Выбрать
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="clearEmployee()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div id="employeeError" class="invalid-feedback" style="display: none;">Пожалуйста, выберите сотрудника</div>
                            </div>
                            
                            <div class="form-group">
                                <label>Передаваемые позиции</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="itemsDisplay" readonly placeholder="Выберите оборудование">
                                    <input type="hidden" name="items" id="itemsInput" required>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#itemsModal">
                                        Выбрать
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="clearItems()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div id="itemsError" class="invalid-feedback" style="display: none;">Пожалуйста, выберите хотя бы один элемент</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="transfer_date">Дата передачи</label>
                                <input type="date" class="form-control" name="transfer_date" id="transfer_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <?php if ($type == 'equipment_temporary'): ?>
                            <div class="form-group">
                                <label for="return_date">Дата возврата</label>
                                <input type="date" class="form-control" name="return_date" id="return_date" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="comments">Комментарий</label>
                                <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-pdf"></i> Сформировать PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Модальное окно для выбора оборудования -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор оборудования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="itemSearch" placeholder="Поиск по названию..." onkeyup="filterItemsTable()">
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Наименование</th>
                                <th>Инв. номер</th>
                                <th>Выбрать</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= isset($item['inventory_number']) ? htmlspecialchars($item['inventory_number']) : '' ?></td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input item-checkbox" 
                                               id="item_<?= $item['id'] ?>" 
                                               value="<?= $item['id'] ?>"
                                               data-name="<?= htmlspecialchars($item['name']) ?>"
                                               <?= isset($item['inventory_number']) ? 'data-inventory="'.htmlspecialchars($item['inventory_number']).'"' : '' ?>
                                               <?= isset($item['quantity']) ? 'data-quantity="'.htmlspecialchars($item['quantity']).'"' : '' ?>>
                                        <label class="form-check-label" for="item_<?= $item['id'] ?>"></label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveItemsSelection()">Сохранить выбор</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для выбора сотрудника -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор сотрудника</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="employeeSearch" placeholder="Поиск по фамилии..." onkeyup="filterEmployeeTable()">
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Фамилия</th>
                                <th>Имя</th>
                                <th>Отчество</th>
                                <th>Выбрать</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= htmlspecialchars($employee['last_name']) ?></td>
                                <td><?= htmlspecialchars($employee['first_name']) ?></td>
                                <td><?= htmlspecialchars($employee['middle_name'] ?? '') ?></td>
                                <td>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input employee-radio" 
                                               name="selectedEmployee" 
                                               id="employee_<?= $employee['id'] ?>" 
                                               value="<?= $employee['id'] ?>"
                                               data-lastname="<?= htmlspecialchars($employee['last_name']) ?>"
                                               data-firstname="<?= htmlspecialchars($employee['first_name']) ?>"
                                               data-middlename="<?= htmlspecialchars($employee['middle_name'] ?? '') ?>">
                                        <label class="form-check-label" for="employee_<?= $employee['id'] ?>"></label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveEmployeeSelection()">Сохранить выбор</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Фильтрация таблицы оборудования
function filterItemsTable() {
    const input = document.getElementById('itemSearch');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('itemsTableBody');
    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByTagName('td')[0];
        if (nameCell) {
            const txtValue = nameCell.textContent || nameCell.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}

// Фильтрация таблицы сотрудников
function filterEmployeeTable() {
    const input = document.getElementById('employeeSearch');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('employeeTableBody');
    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const lastNameCell = rows[i].getElementsByTagName('td')[0];
        if (lastNameCell) {
            const txtValue = lastNameCell.textContent || lastNameCell.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}

// Сохранение выбора оборудования
function saveItemsSelection() {
    try {
        const selectedItems = [];
        const displayText = [];
        
        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            const item = {
                id: checkbox.value,
                name: checkbox.getAttribute('data-name')
            };
            
            if (checkbox.getAttribute('data-inventory')) {
                item.inventory_number = checkbox.getAttribute('data-inventory');
            }
            
            if (checkbox.getAttribute('data-quantity')) {
                item.quantity = checkbox.getAttribute('data-quantity');
            }
            
            selectedItems.push(item);
            
            let itemText = item.name;
            if (item.inventory_number) {
                itemText += ` (${item.inventory_number})`;
            } else if (item.quantity) {
                itemText += ` (${item.quantity} шт.)`;
            }
            
            displayText.push(itemText);
        });
        
        if (selectedItems.length === 0) {
            alert('Пожалуйста, выберите хотя бы один элемент');
            return;
        }
        
        document.getElementById('itemsDisplay').value = displayText.join(', ');
        document.getElementById('itemsInput').value = JSON.stringify(selectedItems);
        document.getElementById('itemsError').style.display = 'none';
        document.getElementById('itemsDisplay').classList.remove('is-invalid');
        
        // Закрываем модальное окно и удаляем backdrop
        const modal = bootstrap.Modal.getInstance(document.getElementById('itemsModal'));
        modal.hide();
        document.querySelector('.modal-backdrop').remove();
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        
    } catch (e) {
        console.error('Ошибка при сохранении выбора оборудования:', e);
        alert('Произошла ошибка при сохранении выбора');
    }
}

// Сохранение выбора сотрудника
function saveEmployeeSelection() {
    try {
        const selectedRadio = document.querySelector('.employee-radio:checked');
        
        if (!selectedRadio) {
            alert('Пожалуйста, выберите сотрудника');
            return;
        }
        
        const lastName = selectedRadio.getAttribute('data-lastname');
        const firstName = selectedRadio.getAttribute('data-firstname');
        const middleName = selectedRadio.getAttribute('data-middlename');
        
        const fullName = `${lastName} ${firstName} ${middleName}`.trim();
        
        document.getElementById('employeeDisplay').value = fullName;
        document.getElementById('employeeInput').value = selectedRadio.value;
        document.getElementById('employeeError').style.display = 'none';
        document.getElementById('employeeDisplay').classList.remove('is-invalid');
        
        // Закрываем модальное окно и удаляем backdrop
        const modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
        modal.hide();
        document.querySelector('.modal-backdrop').remove();
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        
    } catch (e) {
        console.error('Ошибка при сохранении выбора сотрудника:', e);
        alert('Произошла ошибка при сохранении выбора');
    }
}

// Очистка выбранного оборудования
function clearItems() {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('itemsDisplay').value = '';
    document.getElementById('itemsInput').value = '';
    document.getElementById('itemsError').style.display = 'none';
    document.getElementById('itemsDisplay').classList.remove('is-invalid');
}

// Очистка выбранного сотрудника
function clearEmployee() {
    document.querySelectorAll('.employee-radio').forEach(radio => {
        radio.checked = false;
    });
    document.getElementById('employeeDisplay').value = '';
    document.getElementById('employeeInput').value = '';
    document.getElementById('employeeError').style.display = 'none';
    document.getElementById('employeeDisplay').classList.remove('is-invalid');
}

// Восстановление выбранных элементов при открытии модального окна
document.getElementById('itemsModal').addEventListener('show.bs.modal', function() {
    try {
        const itemsInput = document.getElementById('itemsInput').value;
        if (itemsInput) {
            const savedItems = JSON.parse(itemsInput);
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.checked = savedItems.some(item => item.id === checkbox.value);
            });
        }
    } catch (e) {
        console.error('Ошибка при восстановлении выбора:', e);
    }
});

// Восстановление выбранного сотрудника при открытии модального окна
document.getElementById('employeeModal').addEventListener('show.bs.modal', function() {
    try {
        const employeeId = document.getElementById('employeeInput').value;
        if (employeeId) {
            const radio = document.querySelector(`.employee-radio[value="${employeeId}"]`);
            if (radio) radio.checked = true;
        }
    } catch (e) {
        console.error('Ошибка при восстановлении выбора:', e);
    }
});

// Обработчик для закрытия модальных окон и удаления backdrop
function setupModalCloseHandlers() {
    const modals = ['itemsModal', 'employeeModal'];
    
    modals.forEach(modalId => {
        const modalElement = document.getElementById(modalId);
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        });
    });
}

// Валидация формы
function validateForm() {
    let isValid = true;
    
    if (document.getElementById('employeeInput').value === '') {
        document.getElementById('employeeError').style.display = 'block';
        document.getElementById('employeeDisplay').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('employeeError').style.display = 'none';
        document.getElementById('employeeDisplay').classList.remove('is-invalid');
    }
    
    if (document.getElementById('itemsInput').value === '') {
        document.getElementById('itemsError').style.display = 'block';
        document.getElementById('itemsDisplay').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('itemsError').style.display = 'none';
        document.getElementById('itemsDisplay').classList.remove('is-invalid');
    }
    
    return isValid;
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    setupModalCloseHandlers();
});
</script>

<?php require_once '../../includes/footer.php'; ?>