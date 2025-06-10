<?php
$host = 'localhost';
$dbname = 'tasker';
$user = 'root';
$pass = ''; // Domyślnie w XAMPP nie ma hasła

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>