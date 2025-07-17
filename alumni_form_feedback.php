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
    $mata_kuliah = trim($_POST['mata_kuliah']);
    $rating = intval($_POST['rating']);
    $isi_feedback = trim($_POST['isi_feedback']);

    // Validasi
    if (empty($mata_kuliah) || empty($rating) || empty($isi_feedback)) {
        $message = "Semua kolom wajib diisi.";
        $message_type = "danger";
    } elseif ($rating < 1 || $rating > 5) {
        $message = "Rating tidak valid.";
        $message_type = "danger";
    } else {
        // Insert data ke database
        $sql = "INSERT INTO feedback (alumni_id, mata_kuliah, rating, isi_feedback) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $alumni_id, $mata_kuliah, $rating, $isi_feedback);

        if ($stmt->execute()) {
            $message = "Terima kasih! Feedback Anda telah berhasil dikirim.";
            $message_type = "success";
        } else {
            $message = "Terjadi kesalahan saat mengirim feedback.";
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Daftar mata kuliah (bisa juga diambil dari tabel lain jika ada)
$daftar_matkul = [
    "Pengantar Ilmu Komputer",
    "Algoritma dan Struktur Data",
    "Sistem Operasi",
    "Jaringan Komputer",
    "Basis Data",
    "Pemrograman Web",
    "Rekayasa Perangkat Lunak",
    "Kecerdasan Buatan",
    "Grafika Komputer",
    "Interaksi Manusia dan Komputer"
];

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Feedback Akademik - Pusat Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .main-container { padding-top: 4rem; padding-bottom: 4rem; }
        .form-container { max-width: 700px; margin: auto; background-color: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .rating-group .btn-check + .btn {
            background-color: #e9ecef;
            color: #6c757d;
            border-color: #ced4da;
            font-weight: 500;
        }
        .rating-group .btn-check:checked + .btn {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
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
                    <li class="nav-item"><a class="nav-link" href="alumni_form_data.php">Profil Saya</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-primary" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container main-container">
        <div class="form-container">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Formulir Feedback Akademik</h2>
                <p class="text-muted">Beri masukan untuk pengembangan kurikulum yang lebih baik.</p>
            </div>

            <?php if ($message && $message_type == 'success'): ?>
                <div class="alert alert-success text-center">
                    <p class="mb-0"><?php echo htmlspecialchars($message); ?></p>
                    <a href="alumni_dashboard.php" class="btn btn-success mt-2">Kembali ke Dashboard</a>
                </div>
            <?php else: ?>
                <?php if ($message && $message_type == 'danger'): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <form action="alumni_form_feedback.php" method="POST">
                    <div class="mb-4">
                        <label for="mata_kuliah" class="form-label">Pilih Kurikulum/Mata Kuliah Relevan</label>
                        <select class="form-select" id="mata_kuliah" name="mata_kuliah" required>
                            <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
                            <?php foreach ($daftar_matkul as $matkul): ?>
                                <option value="<?php echo htmlspecialchars($matkul); ?>"><?php echo htmlspecialchars($matkul); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Seberapa Bermanfaat Mata Kuliah Ini?</label>
                        <div class="btn-group w-100 rating-group" role="group">
                            <input type="radio" class="btn-check" name="rating" id="rating1" value="1" autocomplete="off" required><label class="btn" for="rating1">Sangat Tidak Puas</label>
                            <input type="radio" class="btn-check" name="rating" id="rating2" value="2" autocomplete="off"><label class="btn" for="rating2">Tidak Puas</label>
                            <input type="radio" class="btn-check" name="rating" id="rating3" value="3" autocomplete="off"><label class="btn" for="rating3">Netral</label>
                            <input type="radio" class="btn-check" name="rating" id="rating4" value="4" autocomplete="off"><label class="btn" for="rating4">Puas</label>
                            <input type="radio" class="btn-check" name="rating" id="rating5" value="5" autocomplete="off"><label class="btn" for="rating5">Sangat Puas</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="isi_feedback" class="form-label">Tuliskan Feedback Anda</label>
                        <textarea class="form-control" id="isi_feedback" name="isi_feedback" rows="5" placeholder="Contoh: Materi sangat relevan dengan dunia kerja, namun perlu lebih banyak praktik..." required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Submit Feedback</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
