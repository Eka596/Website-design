<?php
session_start();
include('config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Обновление количества товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart_item'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $user_id, $product_id);
    }
    
    if ($stmt->execute()) {
        // Получаем общее количество товаров в корзине
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_count = $result->fetch_assoc()['total'] ?? 0;
        
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
    }
    exit();
}

// Удаление товара из корзины
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = intval($_POST['product_id']);
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    
    if ($stmt->execute()) {
        // Получаем общее количество товаров в корзине
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_count = $result->fetch_assoc()['total'] ?? 0;
        
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
?>