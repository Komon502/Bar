<?php
require '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$action = $_GET['action'];

if ($action == 'approve') {
    $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$id]);
}

header("Location: index.php");
