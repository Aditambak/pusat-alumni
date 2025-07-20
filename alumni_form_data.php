<?php
session_start();
require_once 'config.php';

// Keamanan Halaman Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}

$alumni_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telepon = trim($_POST['no_telepon']);
    $alamat_domisili = trim($_POST['alamat_domisili']);
    
    $status_pekerjaan = trim($_POST['status_pekerjaan']);
    $perusahaan_terkini = trim($_POST['perusahaan_terkini']);
    $posisi_terkini = trim($_POST['posisi_terkini']);
    $tanggal_mulai_kerja_terkini = !empty($_POST['tanggal_mulai_kerja_terkini']) ? $_POST['tanggal_mulai_kerja_terkini'] : null;
    
    $perusahaan_pertama = trim($_POST['perusahaan_pertama']);
    $posisi_pertama = trim($_POST['posisi_pertama']);
    $tanggal_mulai_kerja_pertama = !empty($_POST['tanggal_mulai_kerja_pertama']) ? $_POST['tanggal_mulai_kerja_pertama'] : null;

    // Update data di database
    $sql = "UPDATE alumni SET 
                nama_lengkap = ?, email = ?, no_telepon = ?, alamat_domisili = ?,
                status_pekerjaan = ?, perusahaan_terkini = ?, posisi_terkini = ?, tanggal_mulai_kerja_terkini = ?,
                perusahaan_pertama = ?, posisi_pertama = ?, tanggal_mulai_kerja_pertama = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", 
        $nama_lengkap, $email, $no_telepon, $alamat_domisili,
        $status_pekerjaan, $perusahaan_terkini, $posisi_terkini, $tanggal_mulai_kerja_terkini,
        $perusahaan_pertama, $posisi_pertama, $tanggal_mulai_kerja_pertama,
        $alumni_id
    );

    if ($stmt->execute()) {
        $message = "Data Anda berhasil diperbarui!";
        $message_type = "success";
    } else {
        $message = "Terjadi kesalahan saat memperbarui data.";
        $message_type = "danger";
    }
    $stmt->close();
}

// Ambil data terbaru dari alumni untuk ditampilkan di form
$stmt = $conn->prepare("SELECT * FROM alumni WHERE id = ?");
$stmt->bind_param("i", $alumni_id);
$stmt->execute();
$result = $stmt->get_result();
$alumni_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Data Pribadi & Karir - Pusat Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .main-container { padding-top: 4rem; padding-bottom: 4rem; }
        .form-container { max-width: 800px; margin: auto; }
        .accordion-button:not(.collapsed) { background-color: #e9ecef; color: #000; }
    </style>
</head>
<body>

    <!-- Navigasi Atas -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="alumni_dashboard.php">Tracer Study</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="alumni_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="alumni_lowongan.php">Lowongan Kerja</a></li>
                    <li class="nav-item"><a class="nav-link active" href="alumni_form_data.php">Profil Saya</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-primary" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container main-container">
        <div class="form-container">
            <h2 class="fw-bold mb-4">Formulir Data Pribadi dan Riwayat Pekerjaan</h2>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form action="alumni_form_data.php" method="POST">
                <div class="accordion" id="formDataAccordion">
                    <!-- Bagian Data Pribadi -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Data Pribadi
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#formDataAccordion">
                            <div class="accordion-body">
                                <div class="mb-3"><label for="nim" class="form-label">NIM</label><input type="text" class="form-control" id="nim" value="<?php echo htmlspecialchars($alumni_data['nim']); ?>" disabled></div>
                                <div class="mb-3"><label for="nama_lengkap" class="form-label">Nama Lengkap</label><input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($alumni_data['nama_lengkap']); ?>"></div>
                                <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($alumni_data['email']); ?>"></div>
                                <div class="mb-3"><label for="no_telepon" class="form-label">No. Telepon</label><input type="tel" class="form-control" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($alumni_data['no_telepon'] ?? ''); ?>"></div>
                                <div class="mb-3"><label for="alamat_domisili" class="form-label">Alamat Domisili</label><textarea class="form-control" id="alamat_domisili" name="alamat_domisili" rows="3"><?php echo htmlspecialchars($alumni_data['alamat_domisili'] ?? ''); ?></textarea></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Riwayat Pekerjaan Saat Ini -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Riwayat Pekerjaan Saat Ini
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#formDataAccordion">
                            <div class="accordion-body">
                                <div class="mb-3"><label for="status_pekerjaan" class="form-label">Status Pekerjaan</label><select class="form-select" id="status_pekerjaan" name="status_pekerjaan"><option value="Bekerja" <?php echo ($alumni_data['status_pekerjaan'] == 'Bekerja') ? 'selected' : ''; ?>>Bekerja</option><option value="Belum Bekerja" <?php echo ($alumni_data['status_pekerjaan'] == 'Belum Bekerja') ? 'selected' : ''; ?>>Belum Bekerja</option><option value="Wirausaha" <?php echo ($alumni_data['status_pekerjaan'] == 'Wirausaha') ? 'selected' : ''; ?>>Wirausaha</option></select></div>
                                <div class="mb-3"><label for="perusahaan_terkini" class="form-label">Nama Perusahaan / Usaha</label><input type="text" class="form-control" id="perusahaan_terkini" name="perusahaan_terkini" value="<?php echo htmlspecialchars($alumni_data['perusahaan_terkini'] ?? ''); ?>"></div>
                                <div class="mb-3"><label for="posisi_terkini" class="form-label">Posisi</label><input type="text" class="form-control" id="posisi_terkini" name="posisi_terkini" value="<?php echo htmlspecialchars($alumni_data['posisi_terkini'] ?? ''); ?>"></div>
                                <div class="mb-3"><label for="tanggal_mulai_kerja_terkini" class="form-label">Tanggal Mulai Bekerja</label><input type="date" class="form-control" id="tanggal_mulai_kerja_terkini" name="tanggal_mulai_kerja_terkini" value="<?php echo htmlspecialchars($alumni_data['tanggal_mulai_kerja_terkini'] ?? ''); ?>"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Riwayat Pekerjaan Pertama -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Riwayat Pekerjaan Pertama (Setelah Lulus)
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#formDataAccordion">
                            <div class="accordion-body">
                                <div class="mb-3"><label for="perusahaan_pertama" class="form-label">Nama Perusahaan</label><input type="text" class="form-control" id="perusahaan_pertama" name="perusahaan_pertama" value="<?php echo htmlspecialchars($alumni_data['perusahaan_pertama'] ?? ''); ?>"></div>
                                <div class="mb-3"><label for="posisi_pertama" class="form-label">Posisi</label><input type="text" class="form-control" id="posisi_pertama" name="posisi_pertama" value="<?php echo htmlspecialchars($alumni_data['posisi_pertama'] ?? ''); ?>"></div>
                                <div class="mb-3"><label for="tanggal_mulai_kerja_pertama" class="form-label">Tanggal Mulai Bekerja</label><input type="date" class="form-control" id="tanggal_mulai_kerja_pertama" name="tanggal_mulai_kerja_pertama" value="<?php echo htmlspecialchars($alumni_data['tanggal_mulai_kerja_pertama'] ?? ''); ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
