<?php
session_start();
include('config.php');  // Подключаем конфиг с MySQLi

// Проверка, если пользователь не администратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: home.php');
    exit();
}

// Получаем ID товара для удаления
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header('Location: home.php');
    exit();
}

// Удаляем товар из базы данных
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);

if ($stmt->execute()) {
    header('Location: home.php');
    exit();
} else {
    echo "Ошибка при удалении товара.";
}
?>
