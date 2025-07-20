<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $alumni_id = intval($_GET['id']); 

    if ($action == 'verify') {

        $sql = "UPDATE alumni SET status_verifikasi = 'terverifikasi' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alumni_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Akun berhasil diverifikasi.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal memverifikasi akun.";
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } elseif ($action == 'delete') {

        $sql = "DELETE FROM alumni WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alumni_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Akun berhasil ditolak dan dihapus.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menolak akun.";
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    

    header("Location: admin_verifikasi.php");
    exit;
}


if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

$sql_select = "SELECT id, nim, nama_lengkap, email, angkatan FROM alumni WHERE status_verifikasi = 'belum_diverifikasi' ORDER BY created_at DESC";
$result = $conn->query($sql_select);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - Dasbor Admin</title>
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
        .table-responsive {
            background-color: #fff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .btn-verify {
            background-color: #198754;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
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
                <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home fa-fw"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_verifikasi.php"><i class="fas fa-check-circle fa-fw"></i> Verifikasi Akun</a>
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
            <h2 class="fw-bold mb-4">Verifikasi Akun Alumni</h2>

            <!-- Tampilkan pesan sukses/error -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Angkatan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nim']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['angkatan']); ?></td>
                                    <td class="text-center">
                                        <a href="admin_verifikasi.php?action=verify&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-verify">
                                            <i class="fas fa-check me-1"></i> Verifikasi
                                        </a>
                                        <a href="admin_verifikasi.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-reject" onclick="return confirm('Anda yakin ingin menolak dan menghapus akun ini?');">
                                            <i class="fas fa-times me-1"></i> Tolak
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">Tidak ada akun yang perlu diverifikasi saat ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
