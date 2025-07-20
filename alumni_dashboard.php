<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'alumni') {
   
    header("Location: logout.php");
    exit;
}



$alumni_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama_lengkap FROM alumni WHERE id = ?");
$stmt->bind_param("i", $alumni_id);
$stmt->execute();
$result = $stmt->get_result();
$alumni = $result->fetch_assoc();
$nama_alumni = $alumni['nama_lengkap'] ?? 'Alumni';
$stmt->close();


$lowongan_result = $conn->query("SELECT posisi, perusahaan, lokasi FROM lowongan_pekerjaan WHERE status = 'Aktif' ORDER BY tanggal_posting DESC LIMIT 4");

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pusat Alumni Universitas Ma Chung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .main-container {
            padding-top: 4rem;
            padding-bottom: 4rem;
        }
        .welcome-section h1 {
            font-weight: 700;
        }
        .action-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            background-color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .action-card img {
            max-width: 150px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 1.5rem;
        }
        .action-card h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .job-listing {
            list-style: none;
            padding: 0;
        }
        .job-listing li {
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .job-listing li:last-child {
            border-bottom: none;
        }
        .job-listing .job-title {
            font-weight: 600;
            color: #343a40;
        }
        .job-listing .job-company {
            color: #6c757d;
        }
    </style>
</head>
<body>

    <!-- Navigasi Atas -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="alumni_dashboard.php">Tracer Study</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="alumni_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alumni_lowongan.php">Lowongan Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alumni_profil.php">Profil Saya</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-primary" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container main-container">
        <!-- Bagian Selamat Datang -->
        <section class="welcome-section mb-5">
            <h1>Selamat Datang, <?php echo htmlspecialchars(explode(' ', $nama_alumni)[0]); ?>!</h1>
            <p class="text-muted fs-5">Lengkapi data Anda dan jelajahi peluang karir terbaru.</p>
        </section>

        <!-- Kartu Aksi -->
        <section class="action-cards-section mb-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="action-card">
                        <img src="https://placehold.co/400x300/EBF4FF/333?text=Update+Data" alt="Update Data">
                        <h5>Update Data Pribadi & Karir Anda</h5>
                        <p class="text-muted">Pastikan informasi pribadi dan karir Anda selalu terkini.</p>
                        <a href="alumni_form_data.php" class="btn btn-primary">Isi Formulir</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-card">
                        <img src="https://placehold.co/400x300/E6F7F2/333?text=Feedback" alt="Beri Feedback">
                        <h5>Beri Feedback untuk Kurikulum</h5>
                        <p class="text-muted">Bantu kami meningkatkan kualitas pendidikan dengan masukan Anda.</p>
                        <a href="alumni_form_feedback.php" class="btn btn-primary">Isi Feedback</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-card">
                        <img src="https://placehold.co/400x300/FEFBEA/333?text=Lowongan" alt="Lowongan Terbaru">
                        <h5>Lowongan Terbaru</h5>
                        <p class="text-muted">Temukan peluang karir terbaru yang sesuai dengan minat Anda.</p>
                        <a href="alumni_lowongan.php" class="btn btn-outline-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lowongan Terbaru -->
        <section class="latest-jobs-section">
            <h3 class="fw-bold mb-4">Lowongan Terbaru</h3>
            <div class="card">
                <div class="card-body">
                    <ul class="job-listing">
                        <?php if ($lowongan_result->num_rows > 0): ?>
                            <?php while($row = $lowongan_result->fetch_assoc()): ?>
                                <li>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="job-title"><?php echo htmlspecialchars($row['posisi']); ?></div>
                                            <div class="job-company"><?php echo htmlspecialchars($row['perusahaan']); ?></div>
                                        </div>
                                        <div class="col-md-4 text-md-end text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($row['lokasi'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="text-center text-muted p-3">Saat ini belum ada lowongan terbaru.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
