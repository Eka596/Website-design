<?php
session_start();  // Начало сессии

// Удаляем все данные сессии
session_unset();

// Закрываем сессию
session_destroy();

// Перенаправляем пользователя на главную страницу (или страницу логина)
header('Location: index.php');
exit();
?>
