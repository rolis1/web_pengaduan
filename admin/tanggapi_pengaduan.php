<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}


// Variable untuk mengontrol tampilan SweetAlert
$showAlert = false;
$alertMessage = '';
$alertType = 'success';

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
        $showAlert = true;
        $alertMessage = $error;
        $alertType = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE pengaduan SET tanggapan=?, status='sudah ditanggapi' WHERE id=?");
        $stmt->bind_param("si", $tanggapan, $id);

        if ($stmt->execute()) {
            $showAlert = true;
            $alertMessage = 'Tanggapan berhasil terkirim!';
            $alertType = 'success';
        } else {
            $showAlert = true;
            $alertMessage = 'Gagal mengirim tanggapan: ' . $conn->error;
            $alertType = 'error';
        }
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
                <a href="data_pengguna.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'data_pengguna.php' ? 'active' : ''; ?>">
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

        <div class="main-content2">
            <div class="page-header-tanggapi">
                <h1><i class="fas fa-comment-alt"></i>Tanggapan</h1>
                <p>Silakan berikan tanggapan sesuai dengan isi laporan yang diterima.</p>
            </div>
            
            <div class="form-container">
                <form method="post" enctype="multipart/form-data" class="pengaduan-form">
                    <div class="form-group">
                        <label for="deskripsi"><i class="fas fa-comment"></i>Berikan Tanggapan Terkait Isu</label>
                        <textarea name="tanggapan" required><?= htmlspecialchars($pengaduan['tanggapan'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-reset"><i class="fas fa-undo"></i> Reset</button>
                        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Kirim Tanggapan</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

    <?php if ($showAlert): ?>
    <script>
        window.showAlert = true;
        window.alertData = {
            type: '<?= $alertType ?>',
            message: '<?= $alertMessage ?>'
        };
    </script>
    <?php endif; ?>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

    <script>
        // Fungsi untuk menampilkan SweetAlert jika ada alert data dari PHP
            document.addEventListener('DOMContentLoaded', function() {
                // Cek apakah ada data alert dari PHP
                if (window.showAlert && window.alertData) {
                    // Tentukan jenis icon berdasarkan tipe alert
                    const isSuccess = window.alertData.type === 'success';
                    
                    // Tampilkan SweetAlert
                    Swal.fire({
                        icon: window.alertData.type,
                        title: isSuccess ? 'Berhasil!' : 'Gagal!',
                        text: window.alertData.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4CAF50',
                        timerProgressBar: true
                    }).then((result) => {
                        if (isSuccess) {
                            window.location.href = 'data_pengaduan.php'; // pindah halaman setelah sukses
                        }
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