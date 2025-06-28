<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_login();

require_once '../../models/Consumable.php';
require_once '../../models/ReferenceItem.php';
require_once '../../models/Equipment.php';

$db = (new Database())->connect();
$consumable = new Consumable($db);
$reference = new ReferenceItem($db);
$equipment = new Equipment($db);

$types = $reference->getByType('consumable_type')->fetchAll(PDO::FETCH_ASSOC);
$equipments = $equipment->getAll()->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Добавить расходный материал";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .equipment-item:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="content-header d-flex justify-content-between align-items-center mb-4">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <a href="index.php" class="btn btn-secondary">Назад</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="create_handler.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Тип</label>
                        <select class="form-select" name="type_id">
                            <option value="">Выберите тип</option>
                            <?php foreach($types as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    

                    
                    <div class="mb-3">
                        <label class="form-label">Для оборудования</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="equipment-display" 
                                   placeholder="Оборудование не выбрано" readonly>
                            <input type="hidden" name="equipment_id" id="equipment-id">
                            <button class="btn btn-outline-secondary" type="button" 
                                    data-bs-toggle="modal" data-bs-target="#equipmentModal">
                                Выбрать
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Дата поступления</label>
                        <input type="date" class="form-control" name="receipt_date">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>

        <!-- Модальное окно выбора оборудования -->
        <div class="modal fade" id="equipmentModal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="equipmentModalLabel">Выбор оборудования</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="equipment-search" placeholder="Поиск по названию, инв. номеру или аудитории...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Инв. номер</th>
                                        <th>Аудитория</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody id="equipment-list">
                                    <?php foreach ($equipments as $eq): ?>
                                    <tr class="equipment-item" 
                                        data-id="<?= $eq['id'] ?>" 
                                        data-name="<?= htmlspecialchars($eq['name']) ?>"
                                        onclick="selectEquipment(this)">
                                        <td><?= htmlspecialchars($eq['name']) ?></td>
                                        <td><?= htmlspecialchars($eq['inventory_number']) ?></td>
                                        <td><?= htmlspecialchars($eq['classroom_name'] ?? 'Не указано') ?></td>
                                        <td><?= htmlspecialchars($eq['status_name'] ?? 'Не указано') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Функция выбора оборудования
        function selectEquipment(row) {
            const equipmentId = row.getAttribute('data-id');
            const equipmentName = row.getAttribute('data-name');
            
            document.getElementById('equipment-id').value = equipmentId;
            document.getElementById('equipment-display').value = equipmentName;
            
            // Закрываем модальное окно
            const modal = bootstrap.Modal.getInstance(document.getElementById('equipmentModal'));
            modal.hide();
        }
        
        // Поиск оборудования
        document.getElementById('equipment-search').addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#equipment-list tr');
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const invNumber = row.cells[1].textContent.toLowerCase();
                const classroom = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchText) || invNumber.includes(searchText) || classroom.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>