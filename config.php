<?php
$host = '127.0.0.2:3307'; 
$db_name = 'Diplom';
$username = 'Netsonara';
$password = '1234';

// Подключение к БД через MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>
