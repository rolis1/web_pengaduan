<?php
session_start();
include '../config/db.php';

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

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// Ambil data
$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE id = ? AND user_id = ? AND tanggapan IS NULL");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Data tidak ditemukan atau sudah ditanggapi.";
    exit;
}

$data = $result->fetch_assoc();

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kategori = $_POST["kategori"];
    $fakultas = $_POST["fakultas"];
    $deskripsi = $_POST["deskripsi"];

    // Bagian upload file
    $foto_path = $data['foto']; // Default: tetap pakai yang lama jika tidak upload baru

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('bukti_') . '.' . $ext;
            $target_dir = "../images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . $new_name;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto_path = $new_name;

                // Hapus file lama jika ada dan berbeda
                if (!empty($data['foto']) && file_exists("../images/" . $data['foto'])) {
                    unlink("../images/" . $data['foto']);
                }
            } else {
                $error = "Gagal mengunggah file bukti.";
            }
        } else {
            $error = "Format file tidak valid atau ukuran melebihi 2MB.";
        }
    }

    if (!isset($error)) {
        $stmt = $conn->prepare("UPDATE pengaduan SET kategori = ?, fakultas = ?, deskripsi = ?, foto = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $kategori, $fakultas, $deskripsi, $foto_path, $id, $user_id);

        if ($stmt->execute()) {
            // Simpan informasi sukses di session untuk SweetAlert
            $_SESSION['success_message'] = "Pengaduan berhasil diedit";
            header("Location: riwayat_pengaduan.php");
            exit();
        } else {
            $error = "Gagal menyimpan perubahan.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengaduan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <div class="form-container-edit">
            <div class="form-header-edit">
                <h2><i class="fas fa-edit"></i> Edit Pengaduan</h2>
                <p class="form-subtitle">Perbarui detail pengaduan Anda</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="kategori"><i class="fas fa-tag"></i> Kategori:</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Administrasi" <?= $data['kategori'] == 'Administrasi' ? 'selected' : '' ?>>Administrasi</option>
                        <option value="Akademik" <?= $data['kategori'] == 'Akademik' ? 'selected' : '' ?>>Akademik</option>
                        <option value="Fasilitas" <?= $data['kategori'] == 'Fasilitas' ? 'selected' : '' ?>>Fasilitas</option>
                        <option value="Kekerasan" <?= $data['kategori'] == 'Kekerasan' ? 'selected' : '' ?>>Kekerasan</option>
                        <option value="Pelecehan" <?= $data['kategori'] == 'Pelecehan' ? 'selected' : '' ?>>Pelecehan</option>
                        <option value="Pembulian" <?= $data['kategori'] == 'Pembulian' ? 'selected' : '' ?>>Pembulian</option>
                        <option value="Petugas" <?= $data['kategori'] == 'Petugas' ? 'selected' : '' ?>>Petugas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fakultas"><i class="fas fa-university"></i> Fakultas:</label>
                    <select id="fakultas" name="fakultas" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <option value="Fakultas Ilmu Komputer" <?= $data['fakultas'] == 'Fakultas Ilmu Komputer' ? 'selected' : '' ?>>Fakultas Ilmu Komputer</option>
                        <option value="Fakultas Ekonomi dan Bisnis" <?= $data['fakultas'] == 'Fakultas Ekonomi dan Bisnis' ? 'selected' : '' ?>>Fakultas Ekonomi dan Bisnis</option>
                        <option value="Fakultas Teknik" <?= $data['fakultas'] == 'Fakultas Teknik' ? 'selected' : '' ?>>Fakultas Teknik</option>
                        <option value="Fakultas Hukum" <?= $data['fakultas'] == 'Fakultas Hukum' ? 'selected' : '' ?>>Fakultas Hukum</option>
                        <option value="Fakultas Keguruan dan Ilmu Pendidikan" <?= $data['fakultas'] == 'Fakultas Keguruan dan Ilmu Pendidikan' ? 'selected' : '' ?>>Fakultas Keguruan dan Ilmu Pendidikan</option>
                        <option value="Fakultas Pertanian" <?= $data['fakultas'] == 'Fakultas Pertanian' ? 'selected' : '' ?>>Fakultas Pertanian</option>
                        <option value="Fakultas Ilmu Sosial dan Ilmu Politik" <?= $data['fakultas'] == 'Fakultas Ilmu Sosial dan Ilmu Politik' ? 'selected' : '' ?>>Fakultas Ilmu Sosial dan Ilmu Politik</option>
                        <option value="Fakultas Agama Islam" <?= $data['fakultas'] == 'Fakultas Agama Islam' ? 'selected' : '' ?>>Fakultas Agama Islam</option>
                    </select>
                </div>


                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>

                <div class="form-group file-upload">
                    <?php if (!empty($data['foto'])): ?>
                        <div id="preview-lama" style="margin-top:10px;">
                            <strong>Bukti foto saat ini:</strong><br>
                            <img src="../images/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Bukti" style="max-width: 300px; height: auto; border:1px solid #ccc; padding:5px;">
                        </div>
                    <?php endif; ?>

                    <label for="foto"><i class="fas fa-image"></i> Edit bukti foto (Opsional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="foto" name="foto" class="file-input" accept="image/*,application/pdf">
                        <div class="file-input-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-name">Pilih file...</span>
                        </div>
                    </div>
                    <small>Format: JPG, PNG, atau PDF (Maks. 2MB)</small>

                    <!-- Preview file baru -->
                    <div id="preview-baru" style="margin-top:10px;"></div>
                </div>



                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="riwayat_pengaduan.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
            
            <div class="form-footer">
                <p><i class="fas fa-info-circle"></i> Pengaduan yang sudah ditanggapi tidak dapat diedit.</p>
            </div>
        </div>
    </div>
    
    <!-- JS untuk preview -->
<script>
    document.getElementById('foto').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const previewBaru = document.getElementById('preview-baru');
        const previewLama = document.getElementById('preview-lama');
        const fileNameSpan = document.querySelector('.file-name');

        if (file) {
            fileNameSpan.textContent = file.name;

            // Hilangkan preview lama
            if (previewLama) previewLama.style.display = 'none';

            // Buat preview baru
            previewBaru.innerHTML = '';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '300px';
                    img.style.height = 'auto';
                    img.style.border = '1px solid #ccc';
                    img.style.padding = '5px';
                    previewBaru.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                previewBaru.innerHTML = <p>File PDF dipilih: <strong>${file.name}</strong></p>;
            } else {
                previewBaru.innerHTML = <p style="color:red;">Format file tidak didukung.</p>;
            }
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