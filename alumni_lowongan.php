<?php
session_start();
require_once 'api_helper.php'; 

// Keamanan Halaman Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}

// --- Pengaturan API Careerjet ---
$affid = "MASUKKAN_AFFILIATE_ID_ANDA"; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$keywords = isset($_GET['search']) ? $_GET['search'] : "Software Engineer"; 
$location = isset($_GET['lokasi']) ? $_GET['lokasi'] : "Indonesia"; 

$result = getCareerjetJobs($keywords, $location, $page, $affid);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Karier & Lowongan Pekerjaan - Pusat Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .main-container { padding-top: 4rem; padding-bottom: 4rem; }
        .job-card-api { background-color: #fff; border: 1px solid #e9ecef; border-radius: 1rem; padding: 1.5rem; transition: box-shadow 0.2s; height: 100%; display: flex; flex-direction: column; }
        .job-card-api:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .job-card-api .job-title { font-weight: 600; font-size: 1.1rem; }
        .job-card-api .job-company { color: #6c757d; font-weight: 500; }
        .job-card-api .job-location { color: #6c757d; }
        .job-card-api .job-salary { color: #28a745; font-weight: 500; }
        .job-card-api .job-date { font-size: 0.8rem; color: #6c757d; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="alumni_dashboard.php">Tracer Study</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="alumni_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="alumni_lowongan.php">Lowongan Kerja</a></li>
                    <li class="nav-item"><a class="nav-link" href="alumni_form_data.php">Profil Saya</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-primary" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <section class="text-center mb-4">
            <h1 class="fw-bold">Pusat Karier & Lowongan Pekerjaan</h1>
            <p class="text-muted fs-5">Didukung oleh Careerjet - Mesin pencari lowongan kerja.</p>
        </section>

        <section class="mb-4">
            <form action="alumni_lowongan.php" method="GET" class="row g-3 justify-content-center bg-light p-3 rounded-3">
                <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Jabatan, kata kunci, atau perusahaan" value="<?php echo htmlspecialchars($keywords); ?>"></div>
                <div class="col-md-5"><input type="text" name="lokasi" class="form-control" placeholder="Kota atau negara" value="<?php echo htmlspecialchars($location); ?>"></div>
                <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary">Cari Lowongan</button></div>
            </form>
        </section>

        <section class="job-listings-section">
            <div class="row g-4">
                <?php if (isset($result->jobs) && !empty($result->jobs)): ?>
                    <?php foreach ($result->jobs as $job): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="job-card-api">
                                <h5 class="job-title"><?php echo htmlspecialchars($job->title); ?></h5>
                                <p class="job-company mb-1"><?php echo htmlspecialchars($job->company); ?></p>
                                <p class="job-location mb-2"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job->locations); ?></p>
                                <?php if (!empty($job->salary)): ?>
                                    <p class="job-salary"><i class="fas fa-wallet me-1"></i> <?php echo htmlspecialchars($job->salary); ?></p>
                                <?php endif; ?>
                                <p class="job-date mt-auto text-end">Diposting: <?php echo htmlspecialchars($job->date); ?></p>
                                <div class="d-grid mt-2"><a href="<?php echo htmlspecialchars($job->url); ?>" class="btn btn-outline-primary" target="_blank">Lihat Detail & Lamar</a></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><div class="text-center p-5 bg-white rounded-3"><h4>Lowongan tidak ditemukan.</h4></div></div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
