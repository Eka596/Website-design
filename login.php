<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['id_role'] = $user['id_role'];
        $_SESSION['username'] = $username;

        header('Location: home.php');
        exit();
    } else {
        $error = "Неверный логин или пароль!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h1>Вход</h1>
		
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
		
        <form method="POST" class="auth-form">
            <label for="username">Логин</label>
            <input type="text" name="username" required>

            <label for="password">Пароль</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-login">Войти</button>

            <p class="form-switch">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </form>
		<div class="back-home">
			<a href="index.php" class="btn-back">← На главную</a>
		</div>
    </div>
</body>
</html>
