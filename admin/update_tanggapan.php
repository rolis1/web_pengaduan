<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $tanggapan = $_POST['tanggapan'];
    
    $query = "UPDATE pengaduan SET tanggapan = ?, status = 'sudah ditanggapi', updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $tanggapan, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tanggapan berhasil disimpan";
    } else {
        $_SESSION['error'] = "Gagal menyimpan tanggapan";
    }
    
    header("Location: data_pengaduan.php");
    exit();
}