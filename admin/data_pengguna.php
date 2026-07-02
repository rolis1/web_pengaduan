<?php
session_start();

include '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'] ?? ['username' => 'Guest', 'role' => 'guest'];

// Query untuk mengambil data pengguna dengan prepared statement
$query = $conn->prepare("SELECT * FROM users WHERE role = ? ORDER BY id DESC");
$query->bind_param('s', $role);
$role = 'user';
$query->execute();
$result = $query->get_result();
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
                <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Statistik Pengaduan</span>
                </a>
                <a href="data_pengaduan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'data_pengaduan.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i>
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
                <div class="page-header-datapengguna">
                    <h1><i class="fa-solid fa-users"></i> Data Pengguna</h1>
                    <p>Daftar semua akun pengguna</p>
                </div>

                <?php if ($result->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3>Belum Ada Pengguna</h3>
                </div>
                <?php else: ?>
                <div class="card-riwayat">
                    <div class="filter-container">
                        <div class="filter-row">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" placeholder="Cari pengguna / ID">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="userTable" class="complaint-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">ID Pengguna</th>
                                    <th class="px-4 py-2">Nama</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Fakultas</th>
                                    <th class="px-4 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-4 py-2 align-top"><?= $row['id'] ?></td>
                                    <td class="px-4 py-2 align-top"><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td>
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
    </div>

    <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#userTable tbody tr');
        
        rows.forEach(row => {
            const searchData = row.innerText.toLowerCase();
            row.style.display = searchData.includes(searchValue) ? '' : 'none';
        });
    });

    // Konfirmasi sebelum menghapus data
    function confirmDelete(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus pengguna ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `hapus_pengguna.php?id=${id}`;
            }
        });
    }
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
