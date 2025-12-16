<?php
session_start();
include('config.php');  // Подключаем конфиг с MySQLi

// Проверка, если пользователь не администратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: home.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные с формы
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    // Проверяем, что все данные заполнены
    if (!empty($name) && !empty($description) && !empty($price) && !empty($image_url)) {
        // Добавляем товар в базу
        $sql = "INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssds', $name, $description, $price, $image_url);
        
        if ($stmt->execute()) {
            $success = "Товар успешно добавлен!";
        } else {
            $error = "Ошибка при добавлении товара.";
        }
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}
?>


