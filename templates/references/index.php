<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';

// Проверяем права администратора
require_admin();
$page_title = "Справочники";
?>

<div class="content-header">
    <h1 class="content-title"><?= htmlspecialchars($page_title) ?></h1>
</div>

<div class="row">
    <!-- Карточка ссылки на типы оборудования -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-laptop"></i> Типы оборудования
            </div>
            <div class="card-body">
                <p>Управление типами оборудования для категоризации и фильтрации.</p>
            </div>
            <div class="card-footer">
                <a href="equipment-types.php" class="btn btn-primary">
                    <i class="bi bi-gear"></i> Управление
                </a>
            </div>
        </div>
    </div>

    <!-- Карточка ссылки на модели оборудования -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-box"></i> Модели оборудования
            </div>
            <div class="card-body">
                <p>Управление моделями оборудования для более точной классификации.</p>
            </div>
            <div class="card-footer">
                <a href="equipment-models.php" class="btn btn-primary">
                    <i class="bi bi-gear"></i> Управление
                </a>
            </div>
        </div>
    </div>

    <!-- Карточка ссылки на статусы оборудования -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Статусы оборудования
            </div>
            <div class="card-body">
                <p>Управление возможными статусами оборудования.</p>
            </div>
            <div class="card-footer">
                <a href="equipment-statuses.php" class="btn btn-primary">
                    <i class="bi bi-gear"></i> Управление
                </a>
            </div>
        </div>
    </div>

    <!-- Карточка ссылки на типы расходных материалов -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-droplet"></i> Типы расходных материалов
            </div>
            <div class="card-body">
                <p>Управление типами расходных материалов (картриджи, бумага и т.д.).</p>
            </div>
            <div class="card-footer">
                <a href="consumable-types.php" class="btn btn-primary">
                    <i class="bi bi-gear"></i> Управление
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>