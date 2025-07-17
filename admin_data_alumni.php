<?php
session_start();
require_once 'config.php';

// Keamanan Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logika untuk menghapus alumni
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $alumni_id = intval($_GET['id']);
    $sql_delete = "DELETE FROM alumni WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $alumni_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Data alumni berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus data alumni.";
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("Location: admin_data_alumni.php");
    exit;
}

// Ambil pesan dari session jika ada
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Logika untuk Pencarian dan Filter
$search_query = "";
$filter_angkatan = "";
$params = [];
$types = "";

$sql_base = "SELECT id, nim, nama_lengkap, angkatan, status_pekerjaan, perusahaan_terkini FROM alumni WHERE status_verifikasi = 'terverifikasi'";

// Handle pencarian
if (!empty($_GET['search'])) {
    $search_query = " AND (nama_lengkap LIKE ? OR nim LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    array_push($params, $search_term, $search_term);
    $types .= "ss";
}

// Handle filter angkatan
if (!empty($_GET['angkatan'])) {
    $filter_angkatan = " AND angkatan = ?";
    array_push($params, $_GET['angkatan']);
    $types .= "i";
}

$sql_final = $sql_base . $search_query . $filter_angkatan . " ORDER BY angkatan DESC, nama_lengkap ASC";
$stmt = $conn->prepare($sql_final);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Ambil daftar angkatan untuk dropdown filter
$angkatan_result = $conn->query("SELECT DISTINCT angkatan FROM alumni WHERE status_verifikasi = 'terverifikasi' ORDER BY angkatan DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Alumni - Dasbor Admin</title>
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
        .card-table { background-color: #fff; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-control, .form-select { border-radius: 0.5rem; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header"><h5>Universitas Ma Chung</h5></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home fa-fw"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_verifikasi.php"><i class="fas fa-check-circle fa-fw"></i> Verifikasi Akun</a></li>
            <li class="nav-item"><a class="nav-link active" href="admin_data_alumni.php"><i class="fas fa-users fa-fw"></i> Data Alumni</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_feedback.php"><i class="fas fa-comment-alt fa-fw"></i> Feedback Akademik</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_lowongan.php"><i class="fas fa-briefcase fa-fw"></i> Manajemen Lowongan</a></li>
            <li class="nav-item mt-auto"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Data Alumni</h2>
                <button class="btn btn-primary"><i class="fas fa-plus me-2"></i> Tambah Alumni</button>
            </div>

            <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card card-table">
                <div class="card-header bg-white border-0">
                    <form action="admin_data_alumni.php" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                <select name="angkatan" class="form-select">
                                    <option value="">Semua Angkatan</option>
                                    <?php while($row = $angkatan_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['angkatan']; ?>" <?php echo (isset($_GET['angkatan']) && $_GET['angkatan'] == $row['angkatan']) ? 'selected' : ''; ?>>
                                            <?php echo $row['angkatan']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan NIM atau Nama..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">Terapkan</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Angkatan</th>
                                <th>Status Pekerjaan</th>
                                <th>Perusahaan Terkini</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nim']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['angkatan']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['status_pekerjaan']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['perusahaan_terkini'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                                            <a href="admin_data_alumni.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus data alumni ini secara permanen?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted p-4">Data alumni tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
