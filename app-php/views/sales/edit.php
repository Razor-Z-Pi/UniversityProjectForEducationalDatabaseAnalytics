<?php
require_once '../../includes/header.php';
require_once '../../models/Sale.php';
require_once '../../models/Product.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$saleModel = new Sale();
$productModel = new Product();
$sale = $saleModel->getSaleById($_GET['id']);

if (!$sale) {
    header('Location: index.php');
    exit();
}

$referenceData = $saleModel->getReferenceData();
$products = $productModel->getAllProducts();
$groups = $productModel->getAllGroups();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $required = ['Дата_продажи', 'Номер_клиента', 'id_группа', 'id_артикулы', 
                    'id_скидки', 'цена_за_ед', 'количество'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Поле '$field' обязательно для заполнения");
            }
        }
        
        $data = [
            'Дата_продажи' => $_POST['Дата_продажи'],
            'Номер_клиента' => $_POST['Номер_клиента'],
            'id_группа' => $_POST['id_группа'],
            'id_ед_измерения' => $_POST['id_ед_измерения'] ?? 1,
            'id_артикулы' => $_POST['id_артикулы'],
            'id_скидки' => $_POST['id_скидки'],
            'цена_за_ед' => floatval(str_replace(',', '.', $_POST['цена_за_ед'])),
            'количество' => floatval(str_replace(',', '.', $_POST['количество']))
        ];
        
        if ($data['цена_за_ед'] <= 0) {
            throw new Exception("Цена должна быть больше 0");
        }
        
        if ($data['количество'] <= 0) {
            throw new Exception("Количество должно быть больше 0");
        }
        
        if ($saleModel->updateSale($_GET['id'], $data)) {
            $_SESSION['flash_message'] = showMessage('success', 'Продажа успешно обновлена!');
            header('Location: index.php');
            exit();
        } else {
            throw new Exception("Ошибка при обновлении продажи");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактирование продажи #<?php echo e($sale['id']); ?></h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" id="saleForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Дата_продажи" class="form-label">Дата продажи *</label>
                            <input type="date" class="form-control" id="Дата_продажи" name="Дата_продажи" 
                                   value="<?php echo e($sale['Дата_продажи']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="Номер_клиента" class="form-label">Номер клиента *</label>
                            <input type="text" class="form-control" id="Номер_клиента" name="Номер_клиента" 
                                   value="<?php echo e($sale['Номер_клиента']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_группа" class="form-label">Группа товара *</label>
                            <select class="form-select" id="id_группа" name="id_группа" required onchange="loadProducts()">
                                <option value="">Выберите группу</option>
                                <?php foreach($groups as $group): ?>
                                <option value="<?php echo e($group['id']); ?>"
                                    <?php echo $group['id'] == $sale['id_группа'] ? 'selected' : ''; ?>>
                                    <?php echo e($group['Наименование'] . ' (' . $group['общая_группа'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="id_артикулы" class="form-label">Артикул товара *</label>
                            <select class="form-select" id="id_артикулы" name="id_артикулы" required>
                                <option value="">Загрузка товаров...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="цена_за_ед" class="form-label">Цена за единицу *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="цена_за_ед" name="цена_за_ед" 
                                       required min="0.01" value="<?php echo e($sale['цена_за_ед']); ?>" onchange="calculateTotal()">
                                <span class="input-group-text">₽</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="количество" class="form-label">Количество *</label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" id="количество" name="количество" 
                                       required min="0.001" value="<?php echo e($sale['количество']); ?>" onchange="calculateTotal()">
                                <span class="input-group-text" id="unit_label">шт.</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_ед_измерения" class="form-label">Единица измерения</label>
                            <select class="form-select" id="id_ед_измерения" name="id_ед_измерения">
                                <?php foreach($referenceData['единицы_измерения'] as $unit): ?>
                                <option value="<?php echo e($unit['id']); ?>"
                                    <?php echo $unit['id'] == $sale['id_ед_измерения'] ? 'selected' : ''; ?>>
                                    <?php echo e($unit['Наименование'] . ($unit['сокращение'] ? ' (' . $unit['сокращение'] . ')' : '')); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_скидки" class="form-label">Скидка *</label>
                            <select class="form-select" id="id_скидки" name="id_скидки" required onchange="calculateTotal()">
                                <option value="">Выберите скидку</option>
                                <?php foreach($referenceData['скидки'] as $discount): ?>
                                <option value="<?php echo e($discount['id']); ?>" 
                                    data-discount="<?php echo e($discount['общая_скидка']); ?>"
                                    <?php echo $discount['id'] == $sale['id_скидки'] ? 'selected' : ''; ?>>
                                    <?php echo e($discount['тип_клиента'] . ' - ' . $discount['общая_скидка'] . '% (' . $discount['Город'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Итоговая сумма</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4 class="text-center mb-0" id="total_amount">
                                        <?php echo formatPrice($sale['сумма_со_скидкой']); ?>
                                    </h4>
                                    <small class="text-muted text-center d-block" id="discount_info">
                                        <?php 
                                        $discountAmount = floatval($sale['сумма_без_скидки']) - floatval($sale['сумма_со_скидкой']);
                                        echo "Скидка {$sale['общая_скидка_процент']}% (экономия: " . formatPrice($discountAmount) . ")";
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="index.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Информация о продаже</h6>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?php echo e($sale['id']); ?></p>
                <p><strong>Создана:</strong> <?php echo formatDate($sale['created_at']); ?></p>
                <p><strong>Обновлена:</strong> <?php echo formatDate($sale['updated_at']); ?></p>
                <hr>
                <p><strong>Сумма без скидки:</strong> <?php echo formatPrice($sale['сумма_без_скидки']); ?></p>
                <p><strong>Сумма со скидкой:</strong> <?php echo formatPrice($sale['сумма_со_скидкой']); ?></p>
                <p><strong>Размер скидки:</strong> <span class="badge bg-info"><?php echo formatPercent($sale['общая_скидка_процент']); ?></span></p>
            </div>
        </div>
    </div>
</div>

<script>
let productsData = <?php echo json_encode($products); ?>;

function loadProducts() {
    const groupId = document.getElementById('id_группа').value;
    const productSelect = document.getElementById('id_артикулы');
    const saleProductId = <?php echo json_encode($sale['id_артикулы']); ?>;
    
    productSelect.innerHTML = '<option value="">Выберите артикул</option>';
    
    if (!groupId) {
        productSelect.disabled = true;
        return;
    }
    
    productSelect.disabled = false;
    
    // Фильтруем товары по выбранной группе
    const filteredProducts = productsData.filter(product => product.id_группы == groupId);
    
    if (filteredProducts.length === 0) {
        productSelect.innerHTML = '<option value="">Товары не найдены</option>';
        return;
    }
    
    filteredProducts.forEach(product => {
        const option = document.createElement('option');
        option.value = product.id;
        option.textContent = product.артикул + ' - ' + product.наименование;
        option.selected = (product.id == saleProductId);
        productSelect.appendChild(option);
    });
}

function calculateTotal() {
    const price = parseFloat(document.getElementById('цена_за_ед').value) || 0;
    const quantity = parseFloat(document.getElementById('количество').value) || 0;
    const discountSelect = document.getElementById('id_скидки');
    const selectedOption = discountSelect.options[discountSelect.selectedIndex];
    const discount = selectedOption ? parseFloat(selectedOption.dataset.discount || 0) : 0;
    
    const amountWithoutDiscount = price * quantity;
    const amountWithDiscount = amountWithoutDiscount * (1 - discount / 100);
    
    document.getElementById('total_amount').textContent = 
        amountWithDiscount.toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ₽';
    
    if (discount > 0) {
        const discountAmount = amountWithoutDiscount - amountWithDiscount;
        document.getElementById('discount_info').textContent = 
            `Скидка ${discount}% (экономия: ${discountAmount.toLocaleString('ru-RU')} ₽)`;
    } else {
        document.getElementById('discount_info').textContent = 'Без скидки';
    }
}

// Обновление единицы измерения при выборе
document.getElementById('id_ед_измерения').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const unitLabel = document.getElementById('unit_label');
    unitLabel.textContent = selected.textContent.split('(')[1]?.replace(')', '') || 'шт.';
});

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    calculateTotal();
});
</script>

<?php require_once '../../includes/footer.php'; ?>