<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');


// Ambil total pengaduan milik user yang login
$total_complaints_query = "SELECT COUNT(*) AS total FROM pengaduan WHERE user_id = ?";
$jumlah_pengguna = $conn->query("SELECT COUNT(*) AS total_pengguna FROM users WHERE role = 'user'")->fetch_assoc()['total_pengguna'];
$belum = $conn->query("SELECT COUNT(*) AS belum FROM pengaduan WHERE tanggapan IS NULL OR tanggapan = ''")->fetch_assoc()['belum'];
$sudah = $conn->query("SELECT COUNT(*) AS sudah FROM pengaduan WHERE tanggapan IS NULL OR tanggapan = ''")->fetch_assoc()['sudah'];
$stmt_total = $conn->prepare($total_complaints_query);
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_complaints = $result_total->fetch_assoc()['total'];

// Ambil pengaduan yang sedang diproses milik user yang login
$processing_complaints_query = "SELECT COUNT(*) AS processing FROM pengaduan WHERE status = 'belum ditanggapi' AND user_id = ?";
$stmt_processing = $conn->prepare($processing_complaints_query);
$stmt_processing->bind_param("i", $user_id);
$stmt_processing->execute();
$result_processing = $stmt_processing->get_result();
$processing_complaints = $result_processing->fetch_assoc()['processing'];

// Ambil pengaduan yang sudah selesai milik semua user
$completed_complaints_query = "SELECT COUNT(*) AS completed FROM pengaduan WHERE status = 'sudah ditanggapi' AND user_id = ?";
$total = $conn->query("SELECT COUNT(*) AS total FROM pengaduan")->fetch_assoc()['total'];
// Query fakultas dan jumlah pengaduan
$fakultas_data = $conn->query("SELECT fakultas, COUNT(*) AS jumlah FROM pengaduan GROUP BY fakultas");
$stmt_completed = $conn->prepare($completed_complaints_query);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
$completed_complaints = $result_completed->fetch_assoc()['completed'];

// Query fakultas dan jumlah pengaduan
$fakultas_data = $conn->query("SELECT fakultas, COUNT(*) AS jumlah FROM pengaduan GROUP BY fakultas");

$labels = [];
$data = [];

while ($row = $fakultas_data->fetch_assoc()) {
    $labels[] = $row['fakultas'] ?: 'Tidak Diisi'; // handle null fakultas
    $data[] = $row['jumlah'];
}

// Ambil data pengaduan bulan ini
$currentMonth = date('n'); // Bulan saat ini
$currentYear = date('Y'); // Tahun saat ini

$daily_complaints_query = "
    SELECT DAY(created_at) AS day, COUNT(*) AS total 
    FROM pengaduan 
    WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
    GROUP BY DAY(created_at)
    ORDER BY day ASC";

$stmt_daily = $conn->prepare($daily_complaints_query);
$stmt_daily->bind_param("ii", $currentMonth, $currentYear);
$stmt_daily->execute();
$result_daily = $stmt_daily->get_result();

$days = [];
$complaints = [];
while ($row = $result_daily->fetch_assoc()) {
    $days[] = $row['day'];
    $complaints[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
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
    <div class="dashboard-content">
    <div class="section-header">
        <h2>Dashboard Pengaduan Kampus</h2>
    </div>

    <!-- Cards Section - Moved to the top -->
    <div class="cards-container">
        <div class="card yellow">
            <div class="card-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $jumlah_pengguna; ?></h3>
                <p>Jumlah Pengguna</p>
            </div>
        </div>
        <div class="card purple">
            <div class="card-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $total; ?></h3>
                <p>Total Pengaduan</p>
            </div>
        </div>
        <div class="card orange">
            <div class="card-icon">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $belum; ?></h3>
                <p>Belum ditanggapi</p>
            </div>
        </div>
    </div>
    <div class="cards-container-two">
        <!-- Pie Chart Section -->
        <div class="chart-pie-container">
            <h2>Distribusi Pengaduan Berdasarkan Fakultas</h2>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-pie-container">
            <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2>Histogram Pengaduan Bulan Ini</h2>
                <canvas id="histogramChart" height="100" class="max-w-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('pieChart').getContext('2d');

    const pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($data) ?>,
                backgroundColor: [
                    '#3498db', '#f1c40f', '#e74c3c', '#2ecc71', '#9b59b6', '#e67e22',
                    '#1abc9c', '#34495e', '#95a5a6', '#FF6384'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: false
                }
            }
        }
    });

    // Histogram Chart
    const histogramCtx = document.getElementById('histogramChart').getContext('2d');

const histogramChart = new Chart(histogramCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($days) ?>, // Hari dalam bulan ini
        datasets: [{
            label: 'Jumlah Pengaduan',
            data: <?= json_encode($complaints) ?>, // Data jumlah pengaduan per hari
            backgroundColor: '#3498db',
            borderColor: '#2980b9',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Jumlah Pengaduan'
                },
                beginAtZero: true // Memastikan grafik dimulai dari nol
            }
        },
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Histogram Pengaduan Bulan Ini'
            }
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