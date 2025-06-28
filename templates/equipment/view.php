<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
// require_login(); // Раскомментируйте для проверки авторизации

$page_title = "Просмотр оборудования";

// Получаем ID оборудования из GET-параметра
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo '<div class="alert alert-danger">Не указан ID оборудования</div>';
    require_once '../../includes/footer.php';
    exit();
}
?>

<div class="content-header">
    <h1 class="content-title">Просмотр оборудования</h1>
    <div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
        <?php if(get_current_user_role() === 'admin'): ?>
        <a href="edit.php?id=<?= $id ?>" class="btn btn-primary ms-2" id="editBtn">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Индикатор загрузки -->
<div id="loadingIndicator" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Загрузка...</span>
    </div>
    <p class="mt-2">Загрузка данных...</p>
</div>

<!-- Основной контейнер с контентом (изначально скрыт) -->
<div id="contentContainer" style="display: none;">
    <div class="row">
        <!-- Левая колонка -->
        <div class="col-md-8">
            <!-- Блок основной информации -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Основная информация
                </div>
                <div class="card-body" id="basicInfoContainer"></div>
            </div>
            
            <!-- Блок истории перемещений -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> История перемещений
                </div>
                <div class="card-body" id="historyContainer"></div>
            </div>
        </div>
        
        <!-- Правая колонка -->
        <div class="col-md-4">
            <!-- Блок фотографии -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-image"></i> Фотография
                </div>
                <div class="card-body text-center" id="photoContainer"></div>
            </div>
            
            <!-- Блок сетевых настроек -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-ethernet"></i> Сетевые настройки
                </div>
                <div class="card-body" id="networkContainer"></div>
            </div>
            
            <!-- Блок расходных материалов -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-plug"></i> Расходные материалы
                </div>
                <div class="card-body" id="consumablesContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Контейнер для отображения ошибок -->
<div id="errorContainer" class="alert alert-danger" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const equipmentId = <?= $id ?>;
    const loadingIndicator = document.getElementById('loadingIndicator');
    const errorContainer = document.getElementById('errorContainer');
    const contentContainer = document.getElementById('contentContainer');

    // Показать индикатор загрузки
    loadingIndicator.style.display = 'block';
    errorContainer.style.display = 'none';
    contentContainer.style.display = 'none';

    // Таймаут для запроса (10 секунд)
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Запрос занял слишком много времени')), 10000);
    });

    // Выполняем запрос данных
    Promise.race([
        fetch('view_handler.php?id=' + equipmentId, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-cache'
        }),
        timeoutPromise
    ])
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Полученные данные:', data); // Логирование для отладки
        if (!data.success || !data.data) {
            throw new Error(data.message || 'Неверный формат данных');
        }
        renderEquipmentData(data.data);
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showError(error.message || 'Ошибка загрузки данных');
    });

    /**
     * Отображает данные оборудования на странице
     * @param {Object} data - Данные оборудования
     */
    function renderEquipmentData(data) {
        try {
            const { equipment, history = [], network = null, consumables = [] } = data;

            if (!equipment) throw new Error('Данные оборудования отсутствуют');

            // Основная информация
            document.getElementById('basicInfoContainer').innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Наименование:</div>
                    <div class="col-md-8">${escapeHtml(equipment.name || 'Не указано')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Инвентарный номер:</div>
                    <div class="col-md-8">${escapeHtml(equipment.inventory_number || 'Не указан')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Аудитория:</div>
                    <div class="col-md-8">${escapeHtml(equipment.classroom_name || 'Не указана')}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Ответственный:</div>
                    <div class="col-md-8">
                        ${escapeHtml((equipment.responsible_last_name || '') + ' ' + (equipment.responsible_first_name || ''))}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Стоимость:</div>
                    <div class="col-md-8">${equipment.cost ? formatPrice(equipment.cost) : 'Не указана'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Статус:</div>
                    <div class="col-md-8">
                        <span class="badge bg-${getStatusBadgeClass(equipment.status_name)}">
                            ${escapeHtml(equipment.status_name || 'Не указан')}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Комментарий:</div>
                    <div class="col-md-8">${equipment.comments ? nl2br(escapeHtml(equipment.comments)) : 'Нет комментария'}</div>
                </div>
            `;

            // История перемещений
            document.getElementById('historyContainer').innerHTML = history.length > 0 ? `
                <div class="list-group">
                    ${history.map(item => `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Перемещено из ${escapeHtml(item.from_classroom_name || 'неизвестно')} в ${escapeHtml(item.to_classroom_name || 'неизвестно')}</h6>
                                <small>${formatDateTime(item.changed_at)}</small>
                            </div>
                            <small class="text-muted">Пользователь: ${escapeHtml(item.user_last_name || '')} ${escapeHtml(item.user_first_name || '')}</small>
                            ${item.comments ? `<p class="mb-0 mt-1"><small>Комментарий: ${escapeHtml(item.comments)}</small></p>` : ''}
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="text-muted mb-0">Нет данных о перемещениях</p>';

            // Фотография
            document.getElementById('photoContainer').innerHTML = equipment.photo_path ? `
                <img src="${equipment.photo_path}" alt="Фото оборудования" class="img-fluid rounded" style="max-height: 300px;">
                ${equipment.photo_comment ? `<p class="mt-2">${escapeHtml(equipment.photo_comment)}</p>` : ''}
            ` : `
                <div class="text-muted py-5">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                    <p class="mt-2">Фотография отсутствует</p>
                </div>
            `;

            // Сетевые настройки
            document.getElementById('networkContainer').innerHTML = network ? `
                <div class="row mb-2">
                    <div class="col-4 fw-bold">IP адрес:</div>
                    <div class="col-8">${escapeHtml(network.ip_address || 'Не указан')}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Маска подсети:</div>
                    <div class="col-8">${escapeHtml(network.subnet_mask || 'Не указана')}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4 fw-bold">Шлюз:</div>
                    <div class="col-8">${escapeHtml(network.gateway || 'Не указан')}</div>
                </div>
            ` : '<p class="text-muted mb-0">Сетевые настройки отсутствуют</p>';

           // Расходные материалы 
const consumablesHTML = consumables.length > 0 ? `
    <div class="list-group list-group-flush">
        ${consumables.map(item => `
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${escapeHtml(item.name || 'Без названия')}</h6>
                </div>
                ${item.type_name ? `<small class="text-muted">Тип: ${escapeHtml(item.type_name)}</small>` : ''}
            </div>
        `).join('')}
    </div>
` : '<p class="text-muted mb-0">Нет расходных материалов</p>';
            
            document.getElementById('consumablesContainer').innerHTML = consumablesHTML;

            // Показываем контент и скрываем индикатор загрузки
            loadingIndicator.style.display = 'none';
            contentContainer.style.display = 'block';

        } catch (error) {
            console.error('Render error:', error);
            showError('Ошибка при отображении данных: ' + error.message);
        }
    }

    /**
     * Экранирует HTML-символы для безопасного вывода
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Форматирует цену
     */
    function formatPrice(price) {
        return parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$& ') + ' руб.';
    }

    /**
     * Форматирует дату и время
     */
    function formatDateTime(dateTime) {
        try {
            const date = new Date(dateTime);
            return date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU');
        } catch(e) {
            return dateTime || '';
        }
    }

    /**
     * Преобразует переносы строк в <br>
     */
    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    /**
     * Возвращает класс для badge в зависимости от статуса
     */
    function getStatusBadgeClass(status) {
        if (!status) return 'secondary';
        status = status.toLowerCase();
        if (status.includes('ремонт')) return 'warning';
        if (status.includes('слом')) return 'danger';
        return 'success';
    }

    /**
     * Показывает сообщение об ошибке
     */
    function showError(message) {
        loadingIndicator.style.display = 'none';
        errorContainer.style.display = 'block';
        errorContainer.textContent = message;
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>