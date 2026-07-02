<?php
session_start();
include '../config/db.php';

$success = null;  // Menyimpan pesan sukses login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?"); 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];  // Tambahkan ini
        $_SESSION['user'] = $user;
        $success = "Login berhasil! Selamat datang, " . $user['email'];

        // Tunda redirect agar popup berhasil tampil
        if ($user['role'] === 'admin') {
            $_SESSION['redirect'] = "../admin/dashboard.php";
        } else {
            $_SESSION['redirect'] = "../user/index.php";
        }
    } else {
        $error = "Login gagal! Email atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Pengaduan Kampus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
    <div class="container" id="form-container">
        <!-- Navigation Bar -->
        <div class="panel">
            <!-- Panel Kiri -->
            <div class="split left login">
                <div class="content">
                    <div class="form-container">
                        <h2>Masuk ke Akun Anda</h2>
                        <p>Silakan masukkan email dan password untuk melanjutkan</p>

                        <form action="login.php" method="post">
                            <div class="form-group">
                                <label for="email" class="custom-label">Email</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope icon-left"></i>
                                    <input type="email" id="email" name="email" class="custom-input" placeholder="Masukkan email anda" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="custom-label">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock icon-left"></i>
                                    <input type="password" id="password" name="password" class="custom-input"  placeholder="Masukkan password anda" required>
                                    <i class="fas fa-eye toggle-icon" onclick="togglePassword('password', this)"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn-submit">Masuk</button>
                            </div>
                        </form>

                        <div class="form-footer">
                            <p>Belum punya akun? <a href="register.php" class="switch-link" id="to-register">Daftar</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Kanan -->
            <div class="split right login">
                <div class="content">
                    <div class="image-container">
                        <a href="../home.html">
                            <img src="../Gambar/logo-bg.png" alt="Kampus" class="campus-image">
                        </a>
                    </div>
                    <h2>Sistem Pengaduan Kampus</h2>
                    <p>Platform untuk menyampaikan aspirasi, keluhan, dan masukan untuk kampus yang lebih baik.</p>
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-comment-alt"></i>
                            <span>Sampaikan Aspirasi</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Tracking Status</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <span>Privasi Terjamin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk menampilkan notifikasi, alert, atau mengarahkan pengguna.-->
    <script>
    window.loginError = <?php echo (isset($error) && !empty($error)) ? json_encode($error) : 'null'; ?>;
    window.loginSuccess = <?php echo ($success && !empty($success)) ? json_encode($success) : 'null'; ?>;
    window.loginRedirect = <?php echo (isset($_SESSION['redirect']) && !empty($_SESSION['redirect'])) ? json_encode($_SESSION['redirect']) : 'null'; ?>;
    </script>

    <script src="script.js"></script> <!--yang dimuat di sini-->

</body>
</html>