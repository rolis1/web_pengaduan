<?php
session_start();
include '../config/db.php';

// Reset session register_success jika datang dari halaman lain
if (!isset($_POST['email']) && isset($_SESSION['register_success'])) {
    unset($_SESSION['register_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi password
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password harus minimal 8 karakter']);
        exit;
    }

    if (!preg_match('/[0-9]/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password harus mengandung angka']);
        exit;
    }

    if (!preg_match('/[a-zA-Z]/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password harus mengandung huruf']);
        exit;
    }

    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Password dan konfirmasi tidak cocok.']);
        exit;
    }

    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan!']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = 'user';

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = true;
        $_SESSION['registered_email'] = $email;
        echo json_encode([
            'success' => true, 
            'message' => 'Registrasi berhasil!',
            'email' => $email
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Pengaduan Kampus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="page-transition">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Navigation Bar di luar container utama -->
    <nav class="top-nav-bar">
        <div class="nav-content">
            <a href="../home.html" class="home-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
            <div class="logo">
                <img src="../Gambar/logo-bg.png" alt="Logo" width="40">
                <span>TelISIKA</span>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="panel register-panel">
            <div class="split register-left">
                <div class="content">
                    <div class="form-container" id="form-container">
                        <h2>Buat Akun Baru</h2>
                        <p>Daftar untuk mengakses sistem pengaduan kampus</p>
                        <form id="registerForm" method="post">
                            <div class="form-group">
                                <label for="username" class="custom-label">Nama Lengkap</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user icon-left"></i>
                                    <input type="text" id="username" name="username" class="custom-input" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="custom-label">Email</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope icon-left"></i>
                                    <input type="email" id="email" name="email" class="custom-input" placeholder="Masukkan email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="password-wrapper1">
                                    <label for="password" class="custom-label">Password</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock icon-left"></i>
                                        <input type="password" id="password" name="password" class="custom-input" placeholder="Min. 8 karakter dengan angka" required>
                                    </div>
                                    <i class="fas fa-eye toggle-icon1" onclick="togglePassword('password', this)"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="password-wrapper1">
                                    <label for="confirm_password" class="custom-label">Konfirmasi Password</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock icon-left"></i>
                                        <input type="password" id="confirm_password" name="confirm_password" class="custom-input" placeholder="Konfirmasi password" required>
                                    </div>
                                    <i class="fas fa-eye toggle-icon1" onclick="togglePassword('confirm_password', this)"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn-submit">Daftar</button>
                            </div>
                        </form>
                        <div class="form-footer">
                            <p class="register-text">Sudah punya akun? <a href="login.php" id="to-login" class="switch-link">Masuk</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="split register-right">
                <div class="content">
                    <div class="image-container">
                        <a href="../home.html">
                        <img src="../Gambar/logo-bg.png" alt="Kampus" class="campus-image">
                        </a>
                    </div>
                    <h2>Bergabunglah Bersama Kami</h2>
                    <p>Jadilah bagian dari perubahan kampus yang lebih baik melalui platform pengaduan kami.</p>
                    <div class="benefits">
                        <div class="benefit"><i class="fas fa-bullhorn"></i><span>Suarakan Aspirasi</span></div>
                        <div class="benefit"><i class="fas fa-sync-alt"></i><span>Pantau Progres</span></div>
                        <div class="benefit"><i class="fas fa-university"></i><span>Kembangkan Kampus</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>

    <?php if (isset($_SESSION['register_success'])): ?>
        <script>
            window.onload = function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Akun berhasil dibuat. Silakan login.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'login.php';
                });
            };
        </script>
        <?php unset($_SESSION['register_success']); ?>
    <?php endif; ?>
</b>
</html>