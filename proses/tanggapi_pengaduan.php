<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    die("ID pengaduan tidak valid.");
}

$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$pengaduan = $stmt->get_result()->fetch_assoc();
if (!$pengaduan) {
    die("Pengaduan tidak ditemukan.");
}
$tanggapan = isset($pengaduan['tanggapan']) ? $pengaduan['tanggapan'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggapan = trim($_POST['tanggapan']);
    if (empty($tanggapan)) {
        $error = "Tanggapan tidak boleh kosong.";
    } else {
        $stmt = $conn->prepare("UPDATE pengaduan SET tanggapan=?, status='Ditanggapi' WHERE id=?");
        $stmt->bind_param("si", $tanggapan, $id);
        $stmt->execute();
        header("Location: ../admin/data_pengaduan.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Tanggapi Pengaduan</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        
    </style>
</head>
<body>
<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <a href="#">
                    <img src="../Gambar/logo-bg.png" alt="Logo Kampus" class="logo-img">
                </a>
                <span>Admin Dashboard</span>
            </div>
            
            <div class="sidebar-menu">
                <a href="../admin/dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == '../admin/dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Statistik Pengaduan</span>
                </a>
                <a href="../admin/data_pengaduan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == '../admin/data_pengaduan.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Data Pengaduan</span>
                </a>
                <a href="../auth/logout.php" class="menu-item logout" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="user-profile">
                    <div class="user">
                        <!-- Generate avatar based on first letter of username -->
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        <div class="main-content2">
            <div class="page-header">
                <h1><i class="fas fa-comment-alt"></i> Form Pengaduan</h1>
                <p>Sampaikan pengaduan Anda terkait layanan kampus</p>
            </div>
            
            <div class="form-container">
                <form method="post" enctype="multipart/form-data" class="pengaduan-form">
                    <div class="form-group">
                        <label for="deskripsi"><i class="fas fa-comment"></i>Berikan Tanggapan Terkait Isu</label>
                        <textarea name="tanggapan" required><?= htmlspecialchars($pengaduan['tanggapan'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-reset"><i class="fas fa-undo"></i> Reset</button>
                        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Kirim Pengaduan</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

</body>
</html>