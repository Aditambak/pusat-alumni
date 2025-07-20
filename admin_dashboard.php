<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Cek apakah pengguna adalah admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php"); 
    exit;
}

$admin_username = $_SESSION['username'];


$total_alumni_result = $conn->query("SELECT COUNT(id) as total FROM alumni");
$total_alumni = $total_alumni_result->fetch_assoc()['total'];


$unverified_alumni_result = $conn->query("SELECT COUNT(id) as total FROM alumni WHERE status_verifikasi = 'belum_diverifikasi'");
$unverified_alumni = $unverified_alumni_result->fetch_assoc()['total'];


$feedback_result = $conn->query("SELECT COUNT(id) as total FROM feedback");
$total_feedback = $feedback_result->fetch_assoc()['total'];


$active_jobs_result = $conn->query("SELECT COUNT(id) as total FROM lowongan_pekerjaan WHERE status = 'Aktif'");
$active_jobs = $active_jobs_result->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Admin - Pusat Alumni Universitas Ma Chung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
        .sidebar {
            background-color: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .sidebar .nav-link {
            color: #555;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        .sidebar .nav-link .fa-fw {
            margin-right: 0.5rem;
        }
        .sidebar-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        .sidebar-header h5 {
            font-weight: 700;
        }
        .widget-card {
            background-color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .widget-card:hover {
            transform: translateY(-5px);
        }
        .widget-card h5 {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .widget-card .display-4 {
            font-weight: 700;
            color: #343a40;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigasi -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5>Universitas Ma Chung</h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin_dashboard.php"><i class="fas fa-home fa-fw"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_verifikasi.php"><i class="fas fa-check-circle fa-fw"></i> Verifikasi Akun</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_data_alumni.php"><i class="fas fa-users fa-fw"></i> Data Alumni</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_feedback.php"><i class="fas fa-comment-alt fa-fw"></i> Feedback Akademik</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_lowongan.php"><i class="fas fa-briefcase fa-fw"></i> Manajemen Lowongan</a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Dashboard Admin</h2>
                <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($admin_username); ?>!</span>
            </div>

            <!-- Baris Widget -->
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="widget-card text-center">
                        <h5>Total Alumni Terdata</h5>
                        <p class="display-4"><?php echo $total_alumni; ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="widget-card text-center">
                        <h5>Alumni Belum Diverifikasi</h5>
                        <p class="display-4 text-warning"><?php echo $unverified_alumni; ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="widget-card text-center">
                        <h5>Feedback Baru Masuk</h5>
                        <p class="display-4"><?php echo $total_feedback; ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="widget-card text-center">
                        <h5>Total Lowongan Aktif</h5>
                        <p class="display-4"><?php echo $active_jobs; ?></p>
                    </div>
                </div>
            </div>

           <div class="mt-5 d-flex gap-3">
                 <a href="admin_form_lowongan.php" class="btn btn-primary btn-lg">Tambah Lowongan Baru</a>
                 <a href="laporan_alumni.php" class="btn btn-secondary btn-lg" target="_blank">
                    <i class="fas fa-print me-2"></i> Cetak Laporan
                 </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
