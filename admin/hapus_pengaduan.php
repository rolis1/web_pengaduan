<?php
session_start();
include '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];

if ($conn->query("DELETE FROM pengaduan WHERE id=$id") === TRUE) {
    // Jika berhasil, redirect dengan parameter success
    header("Location: data_pengaduan.php?delete_success=true");
} else {
    // Jika gagal, redirect dengan parameter error
    header("Location: data_pengaduan.php?delete_error=false");
}
exit;
?>
