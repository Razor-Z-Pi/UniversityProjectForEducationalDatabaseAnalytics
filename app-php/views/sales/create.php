<?php
require_once '../../includes/header.php';
require_once '../../models/Sale.php';
require_once '../../models/Product.php';

$saleModel = new Sale();
$productModel = new Product();
$referenceData = $saleModel->getReferenceData();
$products = $productModel->getAllProducts();
$groups = $productModel->getAllGroups();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Проверка обязательных полей
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
        
        if ($saleModel->createSale($data)) {
            $_SESSION['flash_message'] = showMessage('success', 'Продажа успешно добавлена!');
            header('Location: index.php');
            exit();
        } else {
            throw new Exception("Ошибка при добавлении продажи");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создание новой продажи</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" id="saleForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Дата_продажи" class="form-label">Дата продажи *</label>
                            <input type="date" class="form-control" id="Дата_продажи" name="Дата_продажи" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="Номер_клиента" class="form-label">Номер клиента *</label>
                            <input type="text" class="form-control" id="Номер_клиента" name="Номер_клиента" 
                                   required placeholder="CL-0001">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_группа" class="form-label">Группа товара *</label>
                            <select class="form-select" id="id_группа" name="id_группа" required onchange="loadProducts()">
                                <option value="">Выберите группу</option>
                                <?php foreach($groups as $group): ?>
                                <option value="<?php echo e($group['id']); ?>">
                                    <?php echo e($group['Наименование'] . ' (' . $group['общая_группа'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="id_артикулы" class="form-label">Артикул товара *</label>
                            <select class="form-select" id="id_артикулы" name="id_артикулы" required disabled>
                                <option value="">Сначала выберите группу</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="цена_за_ед" class="form-label">Цена за единицу *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="цена_за_ед" name="цена_за_ед" 
                                       required min="0.01" value="0.00" onchange="calculateTotal()">
                                <span class="input-group-text">₽</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="количество" class="form-label">Количество *</label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" id="количество" name="количество" 
                                       required min="0.001" value="1.000" onchange="calculateTotal()">
                                <span class="input-group-text" id="unit_label">шт.</span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="id_ед_измерения" class="form-label">Единица измерения</label>
                            <select class="form-select" id="id_ед_измерения" name="id_ед_измерения">
                                <?php foreach($referenceData['единицы_измерения'] as $unit): ?>
                                <option value="<?php echo e($unit['id']); ?>">
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
                                <option value="<?php echo e($discount['id']); ?>" data-discount="<?php echo e($discount['общая_скидка']); ?>">
                                    <?php echo e($discount['тип_клиента'] . ' - ' . $discount['общая_скидка'] . '% (' . $discount['Город'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Итоговая сумма</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h4 class="text-center mb-0" id="total_amount">0.00 ₽</h4>
                                    <small class="text-muted text-center d-block" id="discount_info"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить продажу
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
                <h6><i class="fas fa-info-circle"></i> Информация</h6>
            </div>
            <div class="card-body">
                <p><strong>Создание продажи:</strong></p>
                <ul>
                    <li>Заполните все обязательные поля (отмечены *)</li>
                    <li>Выберите группу товара для загрузки артикулов</li>
                    <li>Укажите корректную цену и количество</li>
                    <li>Выберите подходящую скидку для клиента</li>
                    <li>Проверьте итоговую сумму перед сохранением</li>
                </ul>
                
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Подсказка:</strong> Итоговая сумма рассчитывается автоматически с учетом выбранной скидки.
                    </small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-plus"></i> Быстрые действия</h6>
            </div>
            <div class="card-body">
                <a href="../products/create.php" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-box"></i> Добавить новый товар
                </a>
                <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="showDiscountForm()">
                    <i class="fas fa-percent"></i> Создать новую скидку
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания скидки -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создание новой скидки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="discountForm">
                    <div class="mb-3">
                        <label for="discount_city" class="form-label">Город *</label>
                        <select class="form-select" id="discount_city" name="id_город" required>
                            <option value="">Выберите город</option>
                            <?php foreach($referenceData['города'] as $city): ?>
                            <option value="<?php echo e($city['id']); ?>">
                                <?php echo e($city['Город'] . ' (' . $city['Федеральный_округ'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount_client_type" class="form-label">Тип клиента *</label>
                        <select class="form-select" id="discount_client_type" name="id_типы_клиентов" required>
                            <option value="">Выберите тип клиента</option>
                            <?php foreach($referenceData['типы_клиентов'] as $type): ?>
                            <option value="<?php echo e($type['id']); ?>">
                                <?php echo e($type['Наименование']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="discount_client" class="form-label">Скидка по группе клиентов (%)</label>
                            <input type="number" step="0.01" class="form-control" id="discount_client" 
                                   name="скидки_по_группе_клиентов" value="0" min="0" max="100">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="discount_purchase" class="form-label">Скидка по сумме покупки (%)</label>
                            <input type="number" step="0.01" class="form-control" id="discount_purchase" 
                                   name="скидки_по_группе_сумме_покупки" value="0" min="0" max="100">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="createDiscount()">Создать</button>
            </div>
        </div>
    </div>
</div>

<script>
let productsData = <?php echo json_encode($products); ?>;
let selectedUnit = '';

function loadProducts() {
    const groupId = document.getElementById('id_группа').value;
    const productSelect = document.getElementById('id_артикулы');
    
    productSelect.innerHTML = '<option value="">Выберите артикул</option>';
    productSelect.disabled = !groupId;
    
    if (!groupId) return;
    
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
        option.dataset.price = product.price || 0;
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

function showDiscountForm() {
    const modal = new bootstrap.Modal(document.getElementById('discountModal'));
    modal.show();
}

function createDiscount() {
    const form = document.getElementById('discountForm');
    const formData = new FormData(form);
    
    fetch('../api/create_discount.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Скидка успешно создана!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при создании скидки');
    });
}

// Обновление единицы измерения при выборе
document.getElementById('id_ед_измерения').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const unitLabel = document.getElementById('unit_label');
    unitLabel.textContent = selected.textContent.split('(')[1]?.replace(')', '') || 'шт.';
});

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>

<?php require_once '../../includes/footer.php'; ?>