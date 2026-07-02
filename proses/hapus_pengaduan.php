<?php
session_start();
include '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$conn->query("DELETE FROM pengaduan WHERE id=$id");
header("Location: ../admin/data_pengaduan.php");
exit;
?>