<?php
session_start();  // Начало сессии

// Проверяем, если пользователь уже вошел
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">Интернет-магазин видеорегистраторов</div>
    </header>
    
    <main>
        <div class="content">
           <h1>Ваш идеальный <span class="highlight">выбор</span> — в одном <span class="highlight">клике</span></h1>

            <p>Пожалуйста, войдите или зарегистрируйтесь, чтобы продолжить.</p>
            <div class="auth-links">
				<a href="login.php" class="btn-login">Войти</a>
				<a href="register.php" class="btn-register">Зарегистрироваться</a>
			</div>
        </div>
    </main>
    
    <footer>
        <p>© 2025 Интернет-магазин видеорегистраторов</p>
    </footer>
</body>
</html>
