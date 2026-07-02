<?php
session_start();
include '../config/db.php';

// Check if user is logged in and has the right role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: riwayat_pengaduan.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// Verify that the complaint belongs to the current user and has not been responded to
$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id = ? AND user_id = ? AND tanggapan IS NULL");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // If complaint doesn't exist, doesn't belong to user, or has been responded to
    header("Location: riwayat_pengaduan.php");
    exit;
}

// Delete the complaint
$stmt = $conn->prepare("DELETE FROM pengaduan WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect with success parameter
    header("Location: riwayat_pengaduan.php?delete_success=true");
    exit;
} else {
    // Handle error (optional)
    header("Location: riwayat_pengaduan.php?delete_error=false");
    $stmt->close();
    $conn->close();
    exit;
}
?>