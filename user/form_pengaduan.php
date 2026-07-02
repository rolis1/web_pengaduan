<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user']['id']; // Ambil dari session

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
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

// Variable untuk mengontrol tampilan SweetAlert
$showAlert = false;
$alertMessage = '';
$alertType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $fakultas = $_POST['fakultas'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $foto = $_FILES['foto']['name'];

    if ($foto) {
        $target = "../images/" . basename($foto);
        move_uploaded_file($_FILES['foto']['tmp_name'], $target);
    }

    $stmt = $conn->prepare("INSERT INTO pengaduan (user_id, nama, email, fakultas, kategori, deskripsi, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $nama, $email, $fakultas, $kategori, $deskripsi, $foto);
    
    if ($stmt->execute()) {
        $showAlert = true;
        $alertMessage = 'Pengaduan berhasil terkirim!';
    } else {
        $showAlert = true;
        $alertMessage = 'Gagal mengirim pengaduan: ' . $conn->error;
        $alertType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <a href="#">
                    <img src="../Gambar/logo-bg.png" alt="Logo Kampus" class="logo-img">
                </a>
                <span>Pengaduan Kampus</span>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="form_pengaduan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'form_pengaduan.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Form Pengaduan</span>
                </a>
                <a href="riwayat_pengaduan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'riwayat_pengaduan.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Pengaduan</span>
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
                    <a href="profile.php" class="user">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                    </a>
                </div>
            </div>

        <div class="main-content2">
            <div class="page-header-form">
                <h1><i class="fas fa-comment-alt"></i> Form Pengaduan</h1>
                <p>Sampaikan pengaduan Anda terkait layanan kampus</p>
            </div>
            
            <div class="form-container">
                <form method="post" enctype="multipart/form-data" class="pengaduan-form">
                    <div class="form-group">
                        <label for="nama"><i class="fas fa-user"></i> Nama (Opsional)</label>
                        <input id="nama" name="nama" placeholder="Masukkan nama Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email (Opsional)</label>
                        <input id="email" name="email" type="email" placeholder="Masukkan email Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="fakultas"><i class="fas fa-university"></i> Fakultas</label>
                        <select id="fakultas" name="fakultas" required>
                            <option value="">-- Pilih Fakultas --</option>
                            <option value="Fakultas Ilmu Komputer">Fakultas Ilmu Komputer</option>
                            <option value="Fakultas Ekonomi dan Bisnis">Fakultas Ekonomi dan Bisnis</option>
                            <option value="Fakultas Teknik">Fakultas Teknik</option>
                            <option value="Fakultas Hukum">Fakultas Hukum</option>
                            <option value="Fakultas Keguruan dan Ilmu Pendidikan">Fakultas Keguruan dan Ilmu Pendidikan</option>
                            <option value="Fakultas Pertanian">Fakultas Pertanian</option>
                            <option value="Fakultas Ilmu Sosial dan Ilmu Politik">Fakultas Ilmu Sosial dan Ilmu Politik</option>
                            <option value="Fakultas Agama Islam">Fakultas Agama Islam</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori"><i class="fas fa-tag"></i> Kategori Pengaduan</label>
                        <select id="kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Fasilitas">Fasilitas</option></option>
                            <option value="Kekerasan">Kekerasan</option>
                            <option value="Pelecehan">Pelecehan</option>                            
                            <option value="Pembulian">Pembulian</option>
                            <option value="Petugas">Petugas</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi"><i class="fas fa-comment"></i> Deskripsi Pengaduan</label>
                        <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan pengaduan Anda secara detail..." required></textarea>
                    </div>
                    
                    <div class="form-group file-upload">
                        <label for="foto"><i class="fas fa-image"></i> Unggah Bukti (Opsional)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="foto" name="foto" class="file-input">
                            <div class="file-input-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-name">Pilih file...</span>
                            </div>
                        </div>
                        <small>Format: JPG, PNG, atau PDF (Maks. 2MB)</small>
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JS -->
    <script src="script.js"></script>
    
    <?php if($showAlert): ?>
    <script>
    // Data untuk SweetAlert dari PHP
    var alertData = {
        type: '<?php echo $alertType; ?>',
        message: '<?php echo $alertMessage; ?>'
    };
    
    // Trigger SweetAlert via global variable yang akan diakses di form_pengaduan.js
    window.showAlert = true;
    window.alertData = alertData;
    </script>
    <?php endif; ?>

</body>
</html>
