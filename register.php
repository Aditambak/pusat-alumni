<?php
// Include file koneksi dan memulai session
require_once 'config.php';
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: alumni_dashboard.php");
    exit;
}

$error_message = '';
$success_message = '';

// Proses registrasi ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan data dari form
    $nim = trim($_POST['nim']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $angkatan = trim($_POST['angkatan']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($nim) || empty($nama_lengkap) || empty($email) || empty($angkatan) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } else {
        // Cek apakah NIM atau Email sudah terdaftar
        $sql = "SELECT id FROM alumni WHERE nim = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nim, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "NIM atau Email sudah terdaftar.";
        } else {
            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data alumni baru ke database
            $sql_insert = "INSERT INTO alumni (nim, nama_lengkap, email, angkatan, password) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssis", $nim, $nama_lengkap, $email, $angkatan, $hashed_password);

            if ($stmt_insert->execute()) {
                $success_message = "Registrasi berhasil! Akun Anda akan segera diverifikasi oleh admin. Silakan login setelah akun Anda aktif.";
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Alumni - Pusat Alumni Universitas Ma Chung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
        }
        .register-form-section {
            padding: 2rem 3rem;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .btn-register {
            background-color: #0d6efd;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="col-lg-7 col-xl-6 mx-auto">
            <div class="card register-card">
                <div class="register-form-section">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary"></i>
                        <h2 class="fw-bold mt-3">Buat Akun Alumni</h2>
                        <p class="text-muted">Bergabunglah dengan jaringan alumni Universitas Ma Chung.</p>
                    </div>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                        <div class="d-grid">
                             <a href="login.php" class="btn btn-primary">Kembali ke Login</a>
                        </div>
                    <?php elseif (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($success_message)): // Sembunyikan form jika registrasi sukses ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa)</label>
                            <input type="text" class="form-control" id="nim" name="nim" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="angkatan" class="form-label">Tahun Angkatan</label>
                            <input type="number" class="form-control" id="angkatan" name="angkatan" min="1990" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-register">Daftar</button>
                        </div>
                        <div class="text-center">
                            <p class="text-muted small">Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a></p>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
