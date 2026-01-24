<?php
$host = 'localhost';
$db   = 'bar_booking';
$user = 'root'; // username ของ XAMPP ปกติคือ root
$pass = '';     // password ของ XAMPP ปกติคือว่าง

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
