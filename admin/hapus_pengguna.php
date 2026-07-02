<?php
session_start();
include '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$conn->query("DELETE FROM users WHERE id=$id");
header("Location: data_pengguna.php");
exit;
?>