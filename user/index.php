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

// Ambil pengaduan yang sudah selesai milik user yang login
$completed_complaints_query = "SELECT COUNT(*) AS completed FROM pengaduan WHERE status = 'sudah ditanggapi' AND user_id = ?";
$stmt_completed = $conn->prepare($completed_complaints_query);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
$completed_complaints = $result_completed->fetch_assoc()['completed'];

// Get monthly/weekly complaint data
$complaint_data = [];
$labels = [];
$data_points = [];
$chart_title = 'Complaint Statistics';

$month_names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

if ($filter == 'monthly') {
    // Monthly data
    $monthly_query = "SELECT 
                        MONTH(created_at) AS month, 
                        COUNT(*) AS count 
                      FROM pengaduan 
                      WHERE user_id = ? AND YEAR(created_at) = ?
                      GROUP BY MONTH(created_at)
                      ORDER BY month";
    $stmt_monthly = $conn->prepare($monthly_query);
    $stmt_monthly->bind_param("ii", $user_id, $year);
    $stmt_monthly->execute();
    $result_monthly = $stmt_monthly->get_result();
    
    // Initialize all months with 0
    $complaint_data = array_fill(1, 12, 0);
    
    while ($row = $result_monthly->fetch_assoc()) {
        $complaint_data[$row['month']] = $row['count'];
    }
    
    $labels = $month_names;
    $data_points = array_values($complaint_data);
    $chart_title = "Pengaduan Per-bulan $year";
} else {
    // Weekly data for selected month
    $weekly_query = "SELECT 
                        WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 AS week_of_month,
                        COUNT(*) AS count 
                      FROM pengaduan 
                      WHERE user_id = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?
                      GROUP BY week_of_month
                      ORDER BY week_of_month";
    $stmt_weekly = $conn->prepare($weekly_query);
    $stmt_weekly->bind_param("iii", $user_id, $month, $year);
    $stmt_weekly->execute();
    $result_weekly = $stmt_weekly->get_result();
    
    // Initialize all weeks with 0 (assuming max 5 weeks in a month)
    $max_weeks = 5;
    $complaint_data = array_fill(1, $max_weeks, 0);
    
    while ($row = $result_weekly->fetch_assoc()) {
        $week_num = min($row['week_of_month'], $max_weeks);
        $complaint_data[$week_num] = $row['count'];
    }
    
    $labels = array_map(function($week) { return "Minggu $week"; }, range(1, $max_weeks));
    $data_points = array_values($complaint_data);
    $month_name = $month_names[$month - 1];
    $chart_title = "Pengaduan Per-minggu $month_name $year";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Complaint System</title>
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
    <div class="dashboard-content">
    <div class="section-header">
        <h2>Dashboard Pengaduan Kampus</h2>
    </div>

    <!-- Cards Section - Moved to the top -->
    <div class="cards-container">
        <div class="card purple">
            <div class="card-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $total_complaints; ?></h3>
                <p>Total Pengaduan</p>
            </div>
        </div>
        <div class="card orange">
            <div class="card-icon">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $processing_complaints; ?></h3>
                <p>Belum ditanggapi</p>
            </div>
        </div>
        <div class="card yellow">
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-info">
                <h3><?php echo $completed_complaints; ?></h3>
                <p>Sudah ditanggapi</p>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="statistics-section">
        <div class="filter-options">
            <form method="get">
                <label>
                    <input type="radio" name="filter" value="monthly" <?php echo $filter == 'monthly' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    Bulan
                </label>
                <label>
                    <input type="radio" name="filter" value="weekly" <?php echo $filter == 'weekly' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    Minggu
                </label>
                
                <?php if ($filter == 'monthly'): ?>
                    <select name="year" onchange="this.form.submit()">
                        <?php 
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--): 
                        ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                <?php else: ?>
                    <select name="year" onchange="this.form.submit()">
                        <?php 
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--): 
                        ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="month" onchange="this.form.submit()">
                        <?php foreach ($month_names as $index => $name): ?>
                            <option value="<?php echo $index + 1; ?>" <?php echo $month == ($index + 1) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </form>
        </div>

        <div class="chart-container">
            <h2><?php echo $chart_title; ?></h2>
            <div class="chart-wrapper">
                <canvas id="complaintChart"></canvas>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Panggil Script Eksternal -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="script.js"></script>

<script id="chartLabels" type="application/json">
    <?php echo json_encode($labels); ?>
</script>
<script id="chartDataSelesai" type="application/json">
    <?php echo json_encode($data_selesai); ?>
</script>
<!-- Add a simple JavaScript to toggle active class for dropdown animation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userElement = document.querySelector('.user');
    
    userElement.addEventListener('click', function() {
        this.classList.toggle('active');
    });
});
</script>

 <script>
        const ctx = document.getElementById('complaintChart').getContext('2d');
        const complaintChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Number of Complaints',
                    data: <?php echo json_encode($data_points); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script> -->
    <!-- <script>
        // Pass PHP data to JavaScript
        const monthlyLabels = <?php echo json_encode($months); ?>;
        const monthlyCounts = <?php echo json_encode($counts); ?>;
        const responseRate = <?php echo $response_rate; ?>;
        const slowResponseRate = <?php echo $slow_response_rate; ?>;
    </script> -->
    <script src="script.js"></script>
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