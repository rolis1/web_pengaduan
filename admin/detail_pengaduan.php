<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!isset($_GET['id'])) {
    echo "Pengaduan tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);
if ($_SESSION['user']['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $user_id = $_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Data pengaduan tidak ditemukan.";
    exit;
}

$row = $result->fetch_assoc();

// Format tanggal jika ada
$tanggal_pengaduan = !empty($row['created_at']) ? date("d F Y, H:i", strtotime($row['created_at'])) : '-';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan #<?= $row['id'] ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="detail_pengaduan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Statistik Pengaduan</span>
                </a>
                <a href="data_pengaduan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'data_pengaduan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i>
                    <span>Data Pengaduan</span>
                </a>
                <a href="data_pengguna.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'data_pengguna.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Data Pengguna</span>
                </a>
                <a href="../auth/logout.php" class="menu-item logout" id="logout-link">
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
        <!-- Main Content -->
        <div class="main-content2">
            <div class="header-detail">
                <h1>Detail Pengaduan</h1>
                <a href="data_pengaduan.php" class="back-button-detail">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="complaint-card">
                <div class="complaint-header">
                    <div class="complaint-date">
                        <i class="fas fa-calendar-alt"></i> <?= $tanggal_pengaduan ?>
                    </div>
                </div>
                
                <div class="complaint-body">
                    <div class="complaint-info">
                        <div class="info-group">
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-user"></i> Nama</div>
                                <div class="info-value"><?= htmlspecialchars($row['nama']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                                <div class="info-value"><?= htmlspecialchars($row['email']) ?></div>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-building"></i> Fakultas</div>
                                <div class="info-value"><?= htmlspecialchars($row['fakultas']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label"><i class="fas fa-tag"></i> Kategori</div>
                                <div class="info-value"><?= htmlspecialchars($row['kategori']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="complaint-description">
                        <div class="description-label"><i class="fas fa-comment-alt"></i> Deskripsi Keluhan</div>
                        <div class="description-content">
                            <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
                        </div>
                    </div>
                    
                    <?php if ($row['foto']): ?>
                        <div class="complaint-attachment">
                            <div class="attachment-label"><i class="fas fa-paperclip"></i> Foto/Lampiran</div>
                            <div class="attachment-content">
                                <img src="../images/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Pengaduan" class="complaint-image" onclick="window.open('../uploads/<?= htmlspecialchars($row['foto']) ?>', '_blank')">
                                <a href="../images/<?= htmlspecialchars($row['foto']) ?>" target="_blank" class="view-full-image">
                                    <i class="fas fa-search-plus"></i> Lihat Gambar Penuh
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="complaint-response">
                    <div class="response-label">
                        <i class="fas fa-reply"></i> Tanggapan Admin:
                    </div>
                    <div class="response-content <?= $row['tanggapan'] ? 'has-response' : 'no-response' ?>">
                        <?php if ($row['tanggapan']): ?>
                            <?= nl2br(htmlspecialchars($row['tanggapan'])) ?>
                        <?php else: ?>
                            <div class="waiting-response">
                                <i class="fas fa-hourglass-half"></i>
                                <span>Belum ada tanggapan</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
                        </div>
    </div>

    <script>
    // Tambahkan efek hover ke gambar
    document.addEventListener('DOMContentLoaded', function() {
        const complaintImage = document.querySelector('.complaint-image');
        if (complaintImage) {
            complaintImage.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.15)';
            });
            
            complaintImage.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.1)';
            });
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
                document.getElementById('logout-link').addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah langsung logout

                    Swal.fire({
                        title: 'Yakin ingin keluar?',
                        text: 'Anda akan keluar dari sistem.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, keluar',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = this.href; // Redirect ke logout.php
                        }
                    });
                });
        </script>
</body>
</html>