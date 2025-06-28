<?php
require_once '../../includes/header.php';
require_once '../../includes/auth.php';
require_admin();

$page_title = "Акты приема-передачи";
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= htmlspecialchars($page_title) ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../dashboard/">Главная</a></li>
                    <li class="breadcrumb-item active">Акты</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Акт приема-передачи оборудования на временное пользование -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-laptop mr-2"></i>Оборудование на временное пользование
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Оформление передачи оборудования сотруднику на временное пользование.</p>
                        <div class="text-center mt-3">
                            <a href="transfer-act-form.php?type=equipment_temporary" class="btn btn-primary">
                                <i class="fas fa-file-contract mr-1"></i> Сформировать акт
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Акт приема-передачи расходных материалов -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-box-open mr-2"></i>Расходные материалы
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Оформление передачи расходных материалов сотруднику.</p>
                        <div class="text-center mt-3">
                            <a href="transfer-act-form.php?type=consumables" class="btn btn-success">
                                <i class="fas fa-file-contract mr-1"></i> Сформировать акт
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Акт приема-передачи оборудования -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-desktop mr-2"></i>Оборудование
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Оформление постоянной передачи оборудования.</p>
                        <div class="text-center mt-3">
                            <a href="transfer-act-form.php?type=equipment" class="btn btn-info">
                                <i class="fas fa-file-contract mr-1"></i> Сформировать акт
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Инструкция
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>Для формирования актов:</p>
                        <ol>
                            <li>Выберите нужный тип акта</li>
                            <li>Укажите сотрудника и передаваемые предметы</li>
                            <li>Нажмите кнопку "Сформировать акт"</li>
                            <li>Сохраните или распечатайте полученный PDF-документ</li>
                        </ol>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-exclamation-circle mr-2"></i> Все акты формируются на основании актуальных данных системы.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Инициализация всплывающих подсказок
    $('[data-toggle="tooltip"]').tooltip();
});
</script>