<?php
require_once '../includes/header.php';
require_once '../models/Product.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$productModel = new Product();
$product = $productModel->getProductById($_GET['id']);
$groups = $productModel->getAllGroups();

if (!$product) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'артикул' => $_POST['артикул'],
        'наименование' => $_POST['наименование'],
        'id_группы' => $_POST['id_группы']
    ];
    
    if ($productModel->updateProduct($_GET['id'], $data)) {
        $_SESSION['flash_message'] = showMessage('success', 'Товар успешно обновлен!');
        header('Location: index.php');
        exit();
    } else {
        $error = 'Ошибка при обновлении товара';
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать товар</h1>
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
                               value="<?php echo htmlspecialchars($product['артикул']); ?>"
                               required maxlength="50">
                    </div>
                    
                    <div class="mb-3">
                        <label for="наименование" class="form-label">Наименование *</label>
                        <input type="text" class="form-control" id="наименование" name="наименование" 
                               value="<?php echo htmlspecialchars($product['наименование']); ?>"
                               required maxlength="200">
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_группы" class="form-label">Группа товара *</label>
                        <select class="form-select" id="id_группы" name="id_группы" required>
                            <option value="">Выберите группу</option>
                            <?php foreach($groups as $group): ?>
                            <option value="<?php echo $group['id']; ?>" 
                                <?php echo $group['id'] == $product['id_группы'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group['Наименование'] . ' (' . $group['общая_группа'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="index.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>