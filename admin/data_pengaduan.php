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


$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


// Simpan tanggapan jika ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_tanggapan'])) {
    $id = $_POST['id_pengaduan'];
    $tanggapan = $_POST['tanggapan'];

    $stmt = $conn->prepare("UPDATE pengaduan SET tanggapan = ?, status = 'sudah ditanggapi' WHERE id = ?");
    $stmt->bind_param("si", $tanggapan, $id);

    $stmt->execute();

    $_SESSION['tanggapan_sukses'] = "Tanggapan berhasil disimpan.";
    header("Location: data_pengaduan.php");
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (is_numeric($search)) {
    $stmt = $conn->prepare("SELECT * FROM pengaduan WHERE 
        id = ? OR
        nama LIKE CONCAT('%', ?, '%') OR 
        email LIKE CONCAT('%', ?, '%') OR 
        fakultas LIKE CONCAT('%', ?, '%') OR 
        kategori LIKE CONCAT('%', ?, '%') 
        ORDER BY id DESC");
    $stmt->bind_param("issss", $search, $search, $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM pengaduan ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
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
        <div class="main-content2">
            <div class="page-header-datapengguna">
                <h1><i class="fa-solid fa-database"></i> Data Pengaduan</h1>
                <p>Data Pengaduan Pengguna</p>
            </div>
            <?php if ($result->num_rows === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Belum Ada Pengaduan</h3>
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
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Fakultas</th>
                            <th class="px-4 py-2">Kategori</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $status = $row['tanggapan'] ? 'responded' : 'pending';
                                $statusText = $row['tanggapan'] ? 'Sudah Ditanggapi' : 'Belum Ditanggapi'; 
                                $statusClass = $row['tanggapan'] ? 'status-responded' : 'status-pending';
                                $tanggal = isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : 
                                            (isset($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : 'N/A');
                            ?>
                            <tr data-status="<?= $status ?>" data-search="<?= strtolower(htmlspecialchars($row['id'] . ' ' . $row['nama'] . ' ' . $row['kategori'] . ' ' . $row['fakultas'])) ?>">
                            <td class="px-4 py-2 align-top"><?= $row['id'] ?></td>
                                <td class="px-4 py-2 align-top"><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['fakultas']) ?></td>
                                <td><span class="category-badge"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                <td>
                                    <a href="detail_pengaduan.php?id=<?= $row['id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                        <a href="tanggapi_pengaduan.php?id=<?= $row['id'] ?>" class="btn-edit" style="margin-left: 5px;">
                                            <i class="fas fa-edit"></i> Tanggapi
                                        </a>
                                        <a href="javascript:void(0)" class="btn-delete" style="margin-left: 5px;" onclick="confirmDelete(<?= $row['id'] ?>)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
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

    <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#complaintTable tbody tr');
        
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            if (searchData.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        const filterValue = this.value;
        const rows = document.querySelectorAll('#complaintTable tbody tr');
        
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            if (filterValue === 'all' || status === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Modal functionality
    const modal = document.getElementById('complaintModal');
    const closeBtn = document.querySelector('.close');
    const detailBtns = document.querySelectorAll('.btn-view');
    
    detailBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetchComplaintDetails(id);
            modal.style.display = 'block';
        });
    });
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    </script>
    

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Cek parameter URL untuk menampilkan notifikasi
    function checkDeleteStatus() {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('delete_success') && urlParams.get('delete_success') === 'true') {
            showDeleteSuccessNotification();
            // Hilangkan parameter dari URL setelah ditampilkan
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        if (urlParams.has('delete_error') && urlParams.get('delete_error') === 'false') {
            showDeleteErrorNotification();
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // Konfirmasi sebelum menghapus data
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
                // Arahkan ke script penghapusan dengan ID
                window.location.href = `hapus_pengaduan.php?id=${id}`;
            }
        });
    }

    // Notifikasi jika penghapusan berhasil
    function showDeleteSuccessNotification() {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Pengaduan berhasil dihapus',
            icon: 'success',
            confirmButtonColor: '#4CAF50',
            showConfirmButton: true
        });
    }

    // Notifikasi jika penghapusan gagal
    function showDeleteErrorNotification() {
        Swal.fire({
            title: 'Gagal!',
            text: 'Pengaduan gagal dihapus',
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }

    // Jalankan saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function () {
        checkDeleteStatus();
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