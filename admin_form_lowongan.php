<?php
session_start();
require_once 'config.php';

// Keamanan Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$lowongan = [
    'id' => '', 'posisi' => '', 'perusahaan' => '', 'deskripsi' => '', 
    'lokasi' => '', 'jenis_pekerjaan' => '', 'status' => 'Aktif'
];
$page_title = "Tambah Lowongan Baru";
$form_action = "admin_form_lowongan.php";

if (isset($_GET['id'])) {
    $lowongan_id = intval($_GET['id']);
    $sql = "SELECT * FROM lowongan_pekerjaan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lowongan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $lowongan = $result->fetch_assoc();
        $page_title = "Edit Lowongan Pekerjaan";
        $form_action = "admin_form_lowongan.php?id=" . $lowongan_id;
    }
    $stmt->close();
}

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;
    $posisi = trim($_POST['posisi']);
    $perusahaan = trim($_POST['perusahaan']);
    $deskripsi = trim($_POST['deskripsi']);
    $lokasi = trim($_POST['lokasi']);
    $jenis_pekerjaan = trim($_POST['jenis_pekerjaan']);
    $status = trim($_POST['status']);

    // Validasi
    if (empty($posisi) || empty($perusahaan) || empty($status)) {
        $_SESSION['message'] = "Posisi, Perusahaan, dan Status wajib diisi.";
        $_SESSION['message_type'] = "danger";
    } else {
        if (empty($id)) {
            $sql = "INSERT INTO lowongan_pekerjaan (posisi, perusahaan, deskripsi, lokasi, jenis_pekerjaan, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $posisi, $perusahaan, $deskripsi, $lokasi, $jenis_pekerjaan, $status);
            $message_verb = "ditambahkan";
        } else {
            $sql = "UPDATE lowongan_pekerjaan SET posisi=?, perusahaan=?, deskripsi=?, lokasi=?, jenis_pekerjaan=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $posisi, $perusahaan, $deskripsi, $lokasi, $jenis_pekerjaan, $status, $id);
            $message_verb = "diperbarui";
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Lowongan berhasil " . $message_verb . ".";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menyimpan lowongan.";
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
        header("Location: admin_lowongan.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Dasbor Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; }
        .sidebar { background-color: #ffffff; height: 100vh; position: fixed; top: 0; left: 0; width: 250px; padding: 1.5rem; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .main-content { margin-left: 250px; padding: 2rem; }
        .sidebar .nav-link { color: #555; font-weight: 500; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 0.5rem; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background-color: #e9ecef; color: #0d6efd; }
        .sidebar .nav-link .fa-fw { margin-right: 0.5rem; }
        .sidebar-header { margin-bottom: 2rem; text-align: center; }
        .sidebar-header h5 { font-weight: 700; }
        .card-form { background-color: #fff; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header"><h5>Universitas Ma Chung</h5></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home fa-fw"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_verifikasi.php"><i class="fas fa-check-circle fa-fw"></i> Verifikasi Akun</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_data_alumni.php"><i class="fas fa-users fa-fw"></i> Data Alumni</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_feedback.php"><i class="fas fa-comment-alt fa-fw"></i> Feedback Akademik</a></li>
            <li class="nav-item"><a class="nav-link active" href="admin_lowongan.php"><i class="fas fa-briefcase fa-fw"></i> Manajemen Lowongan</a></li>
            <li class="nav-item mt-auto"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4"><?php echo $page_title; ?></h2>
            <div class="card card-form">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST">
                        <input type="hidden" name="id" value="<?php echo $lowongan['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="posisi" class="form-label">Posisi Pekerjaan</label>
                                <input type="text" class="form-control" id="posisi" name="posisi" value="<?php echo htmlspecialchars($lowongan['posisi']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="perusahaan" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control" id="perusahaan" name="perusahaan" value="<?php echo htmlspecialchars($lowongan['perusahaan']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Pekerjaan</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?php echo htmlspecialchars($lowongan['deskripsi']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($lowongan['lokasi']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jenis_pekerjaan" class="form-label">Jenis Pekerjaan</label>
                                <input type="text" class="form-control" id="jenis_pekerjaan" name="jenis_pekerjaan" placeholder="Contoh: Full-time, Part-time" value="<?php echo htmlspecialchars($lowongan['jenis_pekerjaan']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Aktif" <?php echo ($lowongan['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Nonaktif" <?php echo ($lowongan['status'] == 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Lowongan</button>
                            <a href="admin_lowongan.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
