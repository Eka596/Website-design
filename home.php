<?php
session_start();
include('config.php');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['id_role'];
$is_admin = ($user_role == 1);

// Получение списка товаров
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
if ($search) {
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products";
}

$result = $conn->query($sql);
$products = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Обработка действий с корзиной
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('iii', $quantity, $user_id, $product_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $user_id, $product_id, $quantity);
        }
        
        $stmt->execute();
        header("Location: home.php?success=added_to_cart");
        exit();
    }
    
    if (isset($_POST['remove_from_cart'])) {
        $product_id = intval($_POST['product_id']);
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        
        header("Location: home.php?success=removed_from_cart");
        exit();
    }
    
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            
            if ($quantity > 0) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param('iii', $quantity, $user_id, $product_id);
            } else {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param('ii', $user_id, $product_id);
            }
            $stmt->execute();
        }
        header("Location: home.php?success=cart_updated");
        exit();
    }
}

// Обработка действий админа
if ($is_admin) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $price = floatval($_POST['price']);
        $image_url = $conn->real_escape_string($_POST['image_url']);

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssds', $name, $description, $price, $image_url);

        if ($stmt->execute()) {
            header("Location: home.php?success=added");
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
        $product_id = intval($_POST['product_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $price = floatval($_POST['price']);
        $image_url = $conn->real_escape_string($_POST['image_url']);

        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param('ssdsi', $name, $description, $price, $image_url, $product_id);

        if ($stmt->execute()) {
            header("Location: home.php?success=updated");
            exit();
        }
    }

    if (isset($_GET['delete_id'])) {
        $product_id = intval($_GET['delete_id']);
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $product_id);

        if ($stmt->execute()) {
            header("Location: home.php?success=deleted");
            exit();
        }
    }

    $edit_product = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $result = $conn->query("SELECT * FROM products WHERE id = $edit_id");
        $edit_product = $result->fetch_assoc();
    }
}

// Получаем информацию о товарах в корзине
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

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_admin ? 'Админка' : 'Наши товары' ?></title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>
<body>
    <header>
        <div class="logo">Магазин Видеорегистраторов</div>
        <div class="logout">
            <button onclick="openCartModal()" class="cart-button">🛒 Корзина (<?= $cart_count ?>)</button>
            <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>)</a>
        </div>
    </header>

    <main>
        <h1 class="custom-heading">
            <i class="fas fa-video"></i> 
            <?= $is_admin ? 'Панель управления товарами' : 'Наши видеорегистраторы' ?>
        </h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                switch($_GET['success']) {
                    case 'added': echo "✅ Товар успешно добавлен"; break;
                    case 'updated': echo "🔄 Товар успешно обновлен"; break;
                    case 'deleted': echo "🗑️ Товар успешно удален"; break;
                    case 'added_to_cart': echo "🛒 Товар добавлен в корзину"; break;
                    case 'removed_from_cart': echo "❌ Товар удален из корзины"; break;
                    case 'cart_updated': echo "🔄 Корзина обновлена"; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
            <div class="admin-form">
                <h2 class="form-title"><?= $edit_product ? 'Редактирование товара' : 'Добавить новый товар' ?></h2>
                <form method="POST">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Название:</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Описание:</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= 
                            $edit_product ? htmlspecialchars($edit_product['description']) : '' 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Цена (руб):</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" 
                               value="<?= $edit_product ? htmlspecialchars($edit_product['price']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Ссылка на изображение:</label>
                        <input type="text" class="form-control" id="image_url" name="image_url" 
                               value="<?= $edit_product ? htmlspecialchars($edit_product['image_url']) : '' ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-submit" name="<?= $edit_product ? 'edit_product' : 'add_product' ?>">
                        <?= $edit_product ? 'Обновить товар' : 'Добавить товар' ?>
                    </button>
                    
                    <?php if ($edit_product): ?>
                        <a href="home.php" class="btn btn-cancel">Отмена</a>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>

        <h2>Каталог товаров</h2>
        
<form method="GET" class="filter-form">
    <div class="search-container">
        <input type="text" id="search" class="search-input" name="search" placeholder="Поиск...">
        <button class="btn-search" type="submit">Поиск</button>
    

    <?php if (!empty($_GET['search'])): ?>
        <a href="home.php" class="btn btn-cancel">Сбросить</a>
    <?php endif; ?>
	</div>
</form>

		
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
<div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>';">
    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
         alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
    <div class="product-info">
        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
        <p class="product-desc"><?= mb_strimwidth(htmlspecialchars($product['description']), 0, 60, '...') ?></p>
        <div class="product-price"><?= number_format($product['price'], 2, ',', ' ') ?> ₽</div>
        
<div style="margin-top: 10px;" onclick="event.stopPropagation();">
    <input type="hidden" id="product_id_<?= $product['id'] ?>" value="<?= $product['id'] ?>">
    <div style="display: flex; align-items: center;">
        <input type="number" id="quantity_<?= $product['id'] ?>" value="1" min="1" style="width: 40px; margin-right: 10px;">
        <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-submit">Добавить в корзину</button>
    </div>
</div>

        <?php if ($is_admin): ?>
            <div class="product-actions" onclick="event.stopPropagation();">
                <a href="home.php?edit_id=<?= $product['id'] ?>" class="btn btn-edit">Редактировать</a>
                <a href="home.php?delete_id=<?= $product['id'] ?>" class="btn btn-delete" 
                   onclick="return confirm('Точно удалить этот товар?')">Удалить</a>
            </div>
        <?php endif; ?>
    </div>
</div>

                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>В магазине пока нет товаров.</p>
        <?php endif; ?>
    </main>

<!-- Модальное окно корзины -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <!-- Содержимое будет загружаться динамически -->
    </div>
</div>

    <footer>
        <div class="footer-links">
            <a href="#">Контакты</a>
            <a href="#">О нас</a>
            <a href="#">Политика конфиденциальности</a>
        </div>
        <div class="copyright">
            &copy; 2025 Магазин Видеорегистраторов. Все права защищены.
        </div>
    </footer>

    <script>
        // Функции для корзины
        function openCartModal() {
            document.getElementById('cartModal').style.display = 'block';
        }
        
        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('cartModal')) {
                closeCartModal();
            }
        }
        
        // Новая функция для кнопок товаров
        function toggleDesc(productId) {
            const desc = document.getElementById(`desc-${productId}`);
            const btn = desc.previousElementSibling;
            
            if (desc.classList.contains('hidden-desc')) {
                desc.classList.remove('hidden-desc');
                btn.textContent = 'Скрыть описание';
            } else {
                desc.classList.add('hidden-desc');
                btn.textContent = 'Показать описание';
            }
        }
		
function addToCart(productId) {
    const quantity = document.getElementById(`quantity_${productId}`).value;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('add_to_cart', '1');
    
    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Обновляем счетчик корзины
            updateCartCount(data.cart_count);
            
            // Если модальное окно корзины открыто - обновляем его содержимое
            if(document.getElementById('cartModal').style.display === 'block') {
                loadCartContent();
            }
            
            showNotification('Товар добавлен в корзину');
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при добавлении в корзину');
    });
}

function updateCartCount(count) {
    document.querySelector('.cart-button').textContent = `🛒 Корзина (${count})`;
}

async function loadCartContent() {
    try {
        const response = await fetch('get_cart_content.php');
        const content = await response.text();
        document.querySelector('.modal-content').innerHTML = content;
    } catch (error) {
        console.error('Ошибка загрузки корзины:', error);
    }
}

function showNotification(message) {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Показываем уведомление
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Убираем уведомление через 3 секунды
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

function openCartModal() {
    document.getElementById('cartModal').style.display = 'block';
    loadCartContent(); // Загружаем содержимое при открытии
}

// Обработчик изменения количества
function updateQuantityInput(productId, value) {
    const quantity = parseInt(value);
    if (quantity >= 1) {
        updateCartItem(productId, quantity);
    } else {
        // Если ввели 0 или меньше - ставим 1
        document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`).value = 1;
        updateCartItem(productId, 1);
    }
}

// Обновление при ручном вводе
function updateQuantityInput(productId, value) {
    const quantity = parseInt(value);
    if (quantity >= 1) {
        updateCartItem(productId, quantity);
    } else {
        // Если ввели 0 или меньше - ставим 1
        document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`).value = 1;
        updateCartItem(productId, 1);
    }
}

// Отправка запроса на обновление
function updateCartItem(productId, quantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('update_cart_item', '1');
    
    fetch('update_cart_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем счетчик в шапке
            updateCartCount(data.cart_count);
            // Перезагружаем содержимое корзины
            loadCartContent();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при обновлении корзины');
    });
}

// Удаление товара из корзины
function removeFromCart(productId) {
    if (!confirm('Удалить товар из корзины?')) return;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('remove_from_cart', '1');
    
    fetch('update_cart_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            loadCartContent();
            showNotification('Товар удален из корзины');
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при удалении товара');
    });
}

// Оформление заказа
function checkout() {
    alert('Функционал оформления заказа будет реализован позже');
}

// Добавляем обработчики для колесика мыши
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('wheel', function(e) {
            e.preventDefault();
            const productId = this.closest('.cart-item').dataset.productId;
            const change = e.deltaY > 0 ? -1 : 1;
            const newValue = parseInt(this.value) + change;
            
            if (newValue >= 1) {
                this.value = newValue;
                updateCartItem(productId, newValue);
            }
        });
    });
});
    </script>
</body>
</html>