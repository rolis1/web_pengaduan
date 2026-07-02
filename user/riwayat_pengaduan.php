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
$result = $stmt->get_result();x
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
            <div class="page-header-riwayat">
                <h1><i class="fas fa-history"></i> Riwayat Pengaduan</h1>
                <p>Daftar pengaduan yang telah Anda ajukan</p>
            </div>

            <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Belum Ada Pengaduan</h3>
                <p>Anda belum mengajukan pengaduan apapun</p>
                <a href="form_pengaduan.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Buat Pengaduan Baru
                </a>
            </div>
            <?php else: ?>
            <div class="card-riwayat">
                <div class="filter-container">
                    <div class="filter-row">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Cari pengaduan...">
                        </div>
                        <div class="filter-dropdown">
                            <select id="statusFilter">
                                <option value="all">Semua Status</option>
                                <option value="pending">Belum Ditanggapi</option>
                                <option value="responded">Sudah Ditanggapi</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="table-responsive">
                    <table id="complaintTable" class="complaint-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Fakultas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $status = $row['tanggapan'] ? 'responded' : 'pending';
                                $statusText = $row['tanggapan'] ? 'Sudah Ditanggapi' : 'Belum Ditanggapi'; 
                                $statusClass = $row['tanggapan'] ? 'status-responded' : 'status-pending';
                                // Format tanggal jika tersedia, asumsi tanggal disimpan dalam kolom created_at atau tanggal
                                $tanggal = isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : 
                                          (isset($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : 'N/A');
                            ?>
                            <tr data-status="<?= $status ?>" data-search="<?= strtolower(htmlspecialchars($row['kategori'] . ' ' . $row['fakultas'])) ?>">
                                <td><?= $tanggal ?></td>
                                <td><span class="category-badge"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><?= htmlspecialchars($row['fakultas']) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                <td>
                                    <a href="detail_pengaduan.php?id=<?= $row['id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>

                                    <?php if (!$row['tanggapan']): ?>
                                        <a href="edit_pengaduan.php?id=<?= $row['id'] ?>" class="btn-edit" style="margin-left: 5px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="javascript:void(0)" class="btn-delete" style="margin-left: 5px;" onclick="confirmDelete(<?= $row['id'] ?>)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>

                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
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
        text: 'Apakah Anda yakin ingin menghapus pengaduan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to delete script
            window.location.href = `hapus_pengaduan.php?id=${id}`;
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