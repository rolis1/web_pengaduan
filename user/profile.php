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


$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// For debugging purposes - check if the success parameter exists
// $debug = isset($_GET['delete_success']) ? "Success param exists: " . $_GET['delete_success'] : "No success param";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="style.css">
    <style> .profile-card {
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 500px;
        margin: 40px auto;
        font-family: 'Mulish', sans-serif;
        }
        
        .profile-info p {
            font-size: 16px; 
            color: #333;
            margin: 10px 0;
            }
            .profile-actions {
                margin-top: 30px;
                display: flex;
                justify-content: space-between;
                gap: 12px;
            }
            
            .profile-actions a {
                    flex: 1;
                    padding: 10px 16px;
                    border-radius: 8px;
                    color: #fff;
                    text-align: center;
                    font-weight: 600;
                    text-decoration: none;
                    transition: background-color 0.3s ease;
            }
            
                
            .btn-delete-account {
                padding: 8px 12px;
                background-color: #d33;
                color: white;
                border: none;
                border-radius: 6px;
                font-family: 'Mulish', sans-serif;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.3s ease;
                text-decoration: none;
            }
            
            .btn-delete-account:hover {
                background-color: #b22a2a;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
            }
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
            <div class="page-header-riwayat">
                <h1><i class="fa-solid fa-user"></i> Profile</h1>
                <p>Data Akunmu</p>
            </div>

            <div class="profile-card">
                <div class="profile-info">
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                </div>

                <div class="profile-actions">
                    <a href="../auth/logout.php" class="btn-view btn-logout" id="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <a href="hapus_akun.php" class="btn-delete-account" id="hapus-akun-link"> <i class="fas fa-trash-alt"></i> Hapus Akun </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SweetAlert2 JS (add this right before your script.js) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>

    <script>
        // Main document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Setup search filtering
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterComplaints);
    }
    
    // Setup status filtering
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterComplaints);
    }
    
    // Check for delete success message in URL
    checkDeleteStatus();
});

// Filter complaints based on search input and status
function filterComplaints() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('#complaintTable tbody tr');
    
    if (!searchInput || !statusFilter || !rows.length) return;
    
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        const status = row.getAttribute('data-status');
        const matchesSearch = searchText.includes(searchValue);
        const matchesStatus = statusValue === 'all' || status === statusValue;
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

// Check URL for delete status and show appropriate notification
function checkDeleteStatus() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('delete_success') && urlParams.get('delete_success') === 'true') {
        showDeleteSuccessNotification();
        // Remove the parameter from URL to prevent showing the notification again on refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    if (urlParams.has('delete_error') && urlParams.get('delete_error') === 'false') {
        showDeleteErrorNotification();
        // Remove the parameter from URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// Function to confirm deletion with SweetAlert2
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus akun ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to delete script
            window.location.href = `hapus_akun.php?id=${id}`;
        }
    });
}

// Show success notification after deletion
function showDeleteSuccessNotification() {
    Swal.fire({
        title: 'Berhasil!',
        text: 'Pengaduan berhasil dihapus',
        icon: 'success',
        confirmButtonColor: '#4CAF50',
        showConfirmButton: true
    });
}

// Show error notification if deletion fails
function showDeleteErrorNotification() {
    Swal.fire({
        title: 'Gagal!',
        text: 'Pengaduan gagal dihapus',
        icon: 'error',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
}
    </script>

    <script>
    <?php if (isset($_SESSION['success_message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['success_message']; ?>',
        confirmButtonText: 'OK',
        confirmButtonColor: '#4CAF50',
        showConfirmButton: true
    });
    <?php unset($_SESSION['success_message']); ?>
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.btn-logout').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
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
                        window.location.href = button.getAttribute('href');
                    }
                });
            });
        });
        </script>
    <script> function confirmHapusAkun() { return confirm("Apakah Anda yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan."); } </script>
    <script> 
        document.getElementById('hapus-akun-link').addEventListener('click', function(e) {
             e.preventDefault(); Swal.fire({ title: 'Hapus Akun?', text: 'Tindakan ini tidak dapat dibatalkan!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, hapus akun', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { window.location.href = this.href;} }); }); </script>
</body>
</html>