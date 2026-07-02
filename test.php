<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM pengaduan WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengaduan</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-university"></i>
                <span>Campus Complaint</span>
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
                <a href="logout.php" class="menu-item logout" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="page-header">
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
            <div class="card">
                <div class="filter-container">
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
                
                <div class="table-responsive">
                    <table id="complaintTable" class="complaint-table">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                            <tr data-status="<?= $status ?>" data-search="<?= strtolower(htmlspecialchars($row['kategori'] . ' ' . $row['fakultas'] . ' ' . $row['deskripsi'])) ?>">
                                <td>#<?= $row['id'] ?></td>
                                <td><?= $tanggal ?></td>
                                <td><span class="category-badge"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><?= htmlspecialchars($row['fakultas']) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                <td>
                                    <button class="btn-view" data-id="<?= $row['id'] ?>">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </button>
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

    <!-- Modal Detail Pengaduan -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pengaduan</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="complaintDetails"></div>
            </div>
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
    
    function fetchComplaintDetails(id) {
        // Normally this would be an AJAX request to fetch data from the server
        // For demo purposes, we'll just find the data in the existing table
        const rows = document.querySelectorAll('#complaintTable tbody tr');
        let foundRow;
        
        rows.forEach(row => {
            if (row.querySelector('td').textContent === '#' + id) {
                foundRow = row;
            }
        });
        
        if (foundRow) {
            // Find the full data in your PHP array (this is a simplified example)
            // In a real application, you would make an AJAX request to get all details
            <?php 
            $result->data_seek(0); // Reset result pointer
            echo "const complaintData = {";
            while ($row = $result->fetch_assoc()) {
                echo "{$row['id']}: {
                    id: {$row['id']},
                    nama: '".addslashes(htmlspecialchars($row['nama']))."',
                    email: '".addslashes(htmlspecialchars($row['email']))."',
                    fakultas: '".addslashes(htmlspecialchars($row['fakultas']))."',
                    kategori: '".addslashes(htmlspecialchars($row['kategori']))."',
                    deskripsi: '".addslashes(htmlspecialchars($row['deskripsi']))."',
                    foto: '".($row['foto'] ? addslashes($row['foto']) : "")."',
                    tanggapan: '".($row['tanggapan'] ? addslashes(htmlspecialchars($row['tanggapan'])) : "")."'
                },";
            }
            echo "};";
            ?>
            
            const data = complaintData[id];
            
            let statusClass = data.tanggapan ? 'status-responded' : 'status-pending';
            let statusText = data.tanggapan ? 'Sudah Ditanggapi' : 'Belum Ditanggapi';
            
            let detailHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">ID Pengaduan</div>
                        <div class="detail-value">#${data.id}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><span class="status-badge ${statusClass}">${statusText}</span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Nama</div>
                        <div class="detail-value">${data.nama || '-'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">${data.email || '-'}</div>
                    </div>
                    <div class="detail-item full-width">
                        <div class="detail-label">Fakultas</div>
                        <div class="detail-value">${data.fakultas}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Kategori</div>
                        <div class="detail-value"><span class="category-badge">${data.kategori}</span></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-section-title">Deskripsi Pengaduan</div>
                    <div class="detail-description">${data.deskripsi}</div>
                </div>
                
                ${data.foto ? `
                <div class="detail-section">
                    <div class="detail-section-title">Bukti Pendukung</div>
                    <div class="detail-image">
                        <a href="../images/${data.foto}" target="_blank">
                            <img src="../images/${data.foto}" alt="Bukti">
                        </a>
                    </div>
                </div>` : ''}
                
                <div class="detail-section">
                    <div class="detail-section-title">Tanggapan</div>
                    <div class="detail-response ${!data.tanggapan ? 'no-response' : ''}">
                        ${data.tanggapan ? data.tanggapan : '<i class="fas fa-hourglass-half"></i> Belum ada tanggapan'}
                    </div>
                </div>
            `;
            
            document.getElementById('complaintDetails').innerHTML = detailHTML;
        }
    }
    </script>
</body>
</html>