<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = 'Пользователь с таким именем уже существует!';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 2;

        $stmt = $conn->prepare("INSERT INTO users (username, password, id_role) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['id_role'];

            header('Location: home.php');
            exit();
        } else {
            $error = 'Ошибка при регистрации!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h1>Регистрация</h1>
		
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
		
        <form method="POST" action="register.php" class="auth-form">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-register">Зарегистрироваться</button>

            <p class="form-switch">Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
		<div class="back-home">
			<a href="index.php" class="btn-back">← На главную</a>
		</div>
    </div>
</body>
</html>
