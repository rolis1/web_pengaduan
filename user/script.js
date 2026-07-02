document.addEventListener('DOMContentLoaded', function () {

const labels = JSON.parse(document.getElementById('chartLabels').textContent);
const dataSelesai = JSON.parse(document.getElementById('chartDataSelesai').textContent);

const ctx = document.getElementById('complaintsChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pengaduan Selesai',
            data: dataSelesai,
            fill: true,
            borderColor: 'rgba(124, 58, 237, 1)', // ungu
            backgroundColor: 'rgba(124, 58, 237, 0.1)', // bayangan ungu
            tension: 0.4,
            pointBackgroundColor: 'rgba(124, 58, 237, 1)',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // kalau kamu mau label hilang
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});



    const assistantBtn = document.querySelector('.assistant-btn');
    if (assistantBtn) {
        assistantBtn.addEventListener('click', function () {
            alert('Fitur asisten virtual akan segera hadir!');
        });
    }
});

/* Script pengaduan untuk menambahkan kelas status dan interaksi tambahan */

document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan status ke pengaduan
    const tanggapanText = document.querySelector('.container-detail p:nth-child(9)').textContent;
    const statusBadge = tanggapanText.includes('Belum ada tanggapan') ? 'Menunggu' : 'Ditanggapi';
    const statusColor = tanggapanText.includes('Belum ada tanggapan') ? '#f6c23e' : '#1cc88a';
    
    // Set atribut data-status pada elemen ID
    const idElement = document.querySelector('.container-detail p:first-of-type');
    idElement.setAttribute('data-status', statusBadge);
    
    // Buat efek animasi untuk foto
    const imgElement = document.querySelector('.container-detail img');
    if (imgElement) {
        imgElement.addEventListener('click', function() {
            this.classList.toggle('expanded');
            if (this.classList.contains('expanded')) {
                this.style.maxWidth = '80%';
                this.style.cursor = 'zoom-out';
            } else {
                this.style.maxWidth = '300px';
                this.style.cursor = 'zoom-in';
            }
        });
        
        // Tambahkan kursor zoom-in
        imgElement.style.cursor = 'zoom-in';
    }
    
    // Tambahkan animasi hover ke semua paragraf
    const paragraphs = document.querySelectorAll('.container-detail p');
    paragraphs.forEach(p => {
        p.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
});

/**
 * form_pengaduan.js
 * Script untuk menangani interaksi pada halaman form pengaduan
 */

// Fungsi untuk menampilkan nama file yang dipilih
document.getElementById('foto').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'Pilih file...';
    document.querySelector('.file-name').textContent = fileName;
});

// Fungsi untuk menangani reset form
document.querySelector('.btn-reset').addEventListener('click', function() {
    document.querySelector('.file-name').textContent = 'Pilih file...';
});

// Fungsi untuk menampilkan SweetAlert jika ada alert data dari PHP
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah ada data alert dari PHP
    if (window.showAlert && window.alertData) {
        // Tentukan jenis icon berdasarkan tipe alert
        const isSuccess = window.alertData.type === 'success';
        
        // Tampilkan SweetAlert
        Swal.fire({
            icon: window.alertData.type,
            title: isSuccess ? 'Berhasil!' : 'Gagal!',
            text: window.alertData.message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4CAF50',
            timerProgressBar: true
        }).then((result) => {
            // Jika sukses, reset form
            if (isSuccess) {
                document.querySelector('.pengaduan-form').reset();
                document.querySelector('.file-name').textContent = 'Pilih file...';
            }
        });
    }
});

// Validasi form sebelum submit
document.querySelector('.pengaduan-form').addEventListener('submit', function(event) {
    // Ambil nilai form
    const deskripsi = document.getElementById('deskripsi').value;
    
    if (!deskripsi || deskripsi.trim().length < 10) {
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: 'Silakan isi Deskripsi Pengaduan dengan minimal 10 karakter',
            confirmButtonColor: '#4CAF50'
        });
        return false;
    }
    
    // Validasi ukuran file jika ada yang diunggah
    const fileInput = document.getElementById('foto');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size / 1024 / 1024; // ukuran dalam MB
        if (fileSize > 2) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar!',
                text: 'Ukuran file tidak boleh lebih dari 2MB',
                confirmButtonColor: '#4CAF50'
            });
            return false;
        }
    }
    
    // Biarkan form disubmit jika semua validasi berjalan baik
    return true;
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.logout').forEach(function(logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
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
                    window.location.href = logoutBtn.href;
                }
            });
        });
    });
});

