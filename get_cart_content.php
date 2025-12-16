<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    die('Ошибка авторизации');
}

$user_id = $_SESSION['user_id'];

// Получаем содержимое корзины
$cart_items = [];
$total_price = 0;
$cart_count = 0;

$stmt = $conn->prepare("SELECT p.*, c.quantity FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['total_price'] = $row['price'] * $row['quantity'];
    $total_price += $row['total_price'];
    $cart_count += $row['quantity'];
    $cart_items[] = $row;
}
?>

<span class="close" onclick="closeCartModal()">&times;</span>
<h2>Ваша корзина</h2>

<div id="cart-content">
    <?php if (!empty($cart_items)): ?>
        <form id="cart-form">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?= $item['id'] ?>">
    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
    <div class="cart-item-info">
        <strong><?= htmlspecialchars($item['name']) ?></strong>
        <div class="item-description" data-fulltext="<?= htmlspecialchars($item['description']) ?>">
    <?= htmlspecialchars($item['description']) ?>
</div>
        <div class="item-price">Цена: <?= $item['price'] ?> ₽</div>
    </div>
    <div class="cart-item-controls">
        <input type="number" class="quantity-input" 
               value="<?= $item['quantity'] ?>" min="1" 
               onchange="updateQuantityInput(<?= $item['id'] ?>, this.value)">
        <button type="button" class="btn btn-danger" onclick="removeFromCart(<?= $item['id'] ?>)">Удалить</button>
    </div>
    <div class="cart-item-total">
        <?= $item['total_price'] ?> ₽
    </div>
</div>
                    <div class="cart-item-total">
                        Итого: <?= $item['total_price'] ?> ₽
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="cart-total">
                Общая сумма: <?= number_format($total_price, 2, ',', ' ') ?> ₽
            </div>
        </form>
        
        <div class="cart-actions">
            <button type="button" class="btn btn-submit" onclick="checkout()">Оформить заказ</button>
        </div>
    <?php else: ?>
        <p>Ваша корзина пуста.</p>
    <?php endif; ?>
</div>