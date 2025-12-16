<?php
session_start();
include('config.php');

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Обработка добавления в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Проверяем, есть ли товар уже в корзине
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Обновляем количество
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
    } else {
        // Добавляем новый товар
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $user_id, $product_id, $quantity);
    }
    
    if ($stmt->execute()) {
        // Получаем обновленное количество товаров в корзине
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