<?php
session_start();
include '../config/db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM pengaduan WHERE id=$id");
$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $stmt = $conn->prepare("UPDATE pengaduan SET kategori=?, deskripsi=? WHERE id=?");
    $stmt->bind_param("ssi", $kategori, $deskripsi, $id);
    $stmt->execute();
    header("Location: ../admin/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengaduan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2>Edit Pengaduan</h2>

    <form method="post">
        <label for="kategori">Kategori</label>
        <select name="kategori" id="kategori" required>
            <option value="Akademik" <?= $data['kategori'] === 'Akademik' ? 'selected' : '' ?>>Akademik</option>
            <option value="Fasilitas" <?= $data['kategori'] === 'Fasilitas' ? 'selected' : '' ?>>Fasilitas</option>
            <option value="Administrasi" <?= $data['kategori'] === 'Administrasi' ? 'selected' : '' ?>>Administrasi</option>
        </select>

        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="5" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>

        <button type="submit">Update</button>
    </form>

    <a href="../admin/dashboard.php" class="back-link">← Kembali ke Dashboard</a>
</div>
</body>
</html>
