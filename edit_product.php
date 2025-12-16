<?php
session_start();
include('config.php');  // Подключаем конфиг с MySQLi

// Проверка, если пользователь не администратор
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: home.php');
    exit();
}

$error = '';
$success = '';

// Получаем ID товара для редактирования
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header('Location: home.php');
    exit();
}

// Извлекаем данные товара из базы данных
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: home.php');
    exit();
}

$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем новые данные с формы
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    // Проверяем, что все данные заполнены
    if (!empty($name) && !empty($description) && !empty($price) && !empty($image_url)) {
        // Обновляем товар в базе
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdsi', $name, $description, $price, $image_url, $product_id);

        if ($stmt->execute()) {
            $success = "Товар успешно обновлен!";
        } else {
            $error = "Ошибка при обновлении товара.";
        }
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать товар</title>
</head>
<body>
    <h1>Редактировать товар</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php elseif (!empty($success)): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <label for="name">Название товара:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>

        <label for="description">Описание:</label>
        <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea><br>

        <label for="price">Цена:</label>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required><br>

        <label for="image_url">Ссылка на изображение:</label>
        <input type="text" name="image_url" value="<?= htmlspecialchars($product['image_url']) ?>" required><br>

        <button type="submit">Обновить товар</button>
    </form>
</body>
</html>
