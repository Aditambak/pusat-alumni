<?php
session_start();
require_once 'config.php';

// Keamanan Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logika untuk menghapus feedback
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $feedback_id = intval($_GET['id']);
    $sql_delete = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $feedback_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Feedback berhasil dihapus.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus feedback.";
        $_SESSION['message_type'] = "danger";
    }
    $stmt->close();
    header("Location: admin_feedback.php");
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Logika untuk Pencarian dan Filter
$params = [];
$types = "";
$sql_conditions = [];

$sql_base = "SELECT f.id, f.tanggal_submit, f.mata_kuliah, f.rating, f.isi_feedback, a.nama_lengkap, a.angkatan 
             FROM feedback f 
             JOIN alumni a ON f.alumni_id = a.id";

// Handle pencarian
if (!empty($_GET['search'])) {
    $sql_conditions[] = "(f.mata_kuliah LIKE ? OR a.nama_lengkap LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    array_push($params, $search_term, $search_term);
    $types .= "ss";
}

// Handle filter angkatan
if (!empty($_GET['angkatan'])) {
    $sql_conditions[] = "a.angkatan = ?";
    array_push($params, $_GET['angkatan']);
    $types .= "i";
}

$sql_final = $sql_base;
if (!empty($sql_conditions)) {
    $sql_final .= " WHERE " . implode(" AND ", $sql_conditions);
}
$sql_final .= " ORDER BY f.tanggal_submit DESC";

$stmt = $conn->prepare($sql_final);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();


$angkatan_result = $conn->query("SELECT DISTINCT angkatan FROM alumni JOIN feedback ON alumni.id = feedback.alumni_id ORDER BY angkatan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Akademik - Dasbor Admin</title>
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
        .rating-star { color: #ffc107; }
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
            <li class="nav-item"><a class="nav-link active" href="admin_feedback.php"><i class="fas fa-comment-alt fa-fw"></i> Feedback Akademik</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_lowongan.php"><i class="fas fa-briefcase fa-fw"></i> Manajemen Lowongan</a></li>
            <li class="nav-item mt-auto"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4">Feedback Akademik</h2>

            <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card card-table">
                <div class="card-header bg-white border-0">
                    <form action="admin_feedback.php" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <select name="angkatan" class="form-select">
                                <option value="">Filter per Angkatan</option>
                                <?php while($row = $angkatan_result->fetch_assoc()): ?>
                                    <option value="<?php echo $row['angkatan']; ?>" <?php echo (isset($_GET['angkatan']) && $_GET['angkatan'] == $row['angkatan']) ? 'selected' : ''; ?>>
                                        Angkatan <?php echo $row['angkatan']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan mata kuliah atau nama alumni..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Alumni</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center">Rating</th>
                                <th>Isi Feedback</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y', strtotime($row['tanggal_submit'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?> <small class="text-muted">(Angk. <?php echo $row['angkatan']; ?>)</small></td>
                                        <td><?php echo htmlspecialchars($row['mata_kuliah']); ?></td>
                                        <td class="text-center">
                                            <?php for($i = 0; $i < $row['rating']; $i++) echo '<i class="fas fa-star rating-star"></i>'; ?>
                                            <?php for($i = $row['rating']; $i < 5; $i++) echo '<i class="far fa-star rating-star"></i>'; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($row['isi_feedback'], 0, 50)) . '...'; ?></td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                                            <a href="admin_feedback.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus feedback ini?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted p-4">Tidak ada data feedback yang ditemukan.</td>
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
