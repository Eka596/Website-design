<?php
session_start();

// Добавление товара
if (isset($_POST['add_to_cart'])) {
    $item = [
        'id' => $_POST['product_id'],
        'name' => $_POST['product_name'],
        'price' => $_POST['product_price'],
        'quantity' => 1
    ];

    if (isset($_SESSION['cart'])) {
        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $item['id']) {
                $cartItem['quantity']++;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = $item;
        }
    } else {
        $_SESSION['cart'][] = $item;
    }

    header("Location: cart.php");
    exit();
}

// Удаление товара
if (isset($_GET['remove'])) {
    $removeId = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $removeId) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Ваша корзина</h2>
    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= $item['price'] ?> ₽</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= $subtotal ?> ₽</td>
                    <td><a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Удалить</a></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Итого:</strong></td>
                    <td colspan="2"><strong><?= $total ?> ₽</strong></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>Ваша корзина пуста.</p>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary">Продолжить покупки</a>
</div>
</body>
</html>
