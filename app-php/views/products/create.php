<?php
require_once '../includes/header.php';
require_once '../models/Product.php';

$productModel = new Product();
$groups = $productModel->getAllGroups();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'артикул' => $_POST['артикул'],
        'наименование' => $_POST['наименование'],
        'id_группы' => $_POST['id_группы']
    ];
    
    if ($productModel->createProduct($data)) {
        $_SESSION['flash_message'] = showMessage('success', 'Товар успешно добавлен!');
        header('Location: index.php');
        exit();
    } else {
        $error = 'Ошибка при добавлении товара';
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить новый товар</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="артикул" class="form-label">Артикул *</label>
                        <input type="text" class="form-control" id="артикул" name="артикул" 
                               required maxlength="50">
                    </div>
                    
                    <div class="mb-3">
                        <label for="наименование" class="form-label">Наименование *</label>
                        <input type="text" class="form-control" id="наименование" name="наименование" 
                               required maxlength="200">
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_группы" class="form-label">Группа товара *</label>
                        <select class="form-select" id="id_группы" name="id_группы" required>
                            <option value="">Выберите группу</option>
                            <?php foreach($groups as $group): ?>
                            <option value="<?php echo $group['id']; ?>">
                                <?php echo htmlspecialchars($group['Наименование'] . ' (' . $group['общая_группа'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить
                        </button>
                        <a href="index.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>Справка</h6>
            </div>
            <div class="card-body">
                <p>Артикул должен быть уникальным для каждого товара.</p>
                <p>Наименование должно четко описывать товар.</p>
                <p>Выберите соответствующую группу товара.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 