<?php
/*
 * Skrip Utilitas untuk Menghasilkan Hash Password yang Aman
 * Gunakan skrip ini untuk membuat hash dari password apa pun,
 * lalu salin hasilnya ke kolom 'password' di database Anda.
 */

// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$password_to_hash = '';
$hashed_password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['password'])) {
    $password_to_hash = $_POST['password'];
    // Membuat hash password menggunakan algoritma default (saat ini BCRYPT)
    // Ini adalah metode yang direkomendasikan dan aman.
    $hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 5rem;
        }
        .result-box {
            background-color: #e9ecef;
            padding: 1rem;
            border-radius: 0.5rem;
            word-wrap: break-word;
            margin-top: 1.5rem;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">Password Hash Generator</h2>
                <p class="text-center text-muted">Masukkan password untuk di-hash menggunakan `password_hash()`.</p>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="text" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password_to_hash); ?>" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Generate Hash</button>
                    </div>
                </form>

                <?php if ($hashed_password): ?>
                    <div class="mt-4">
                        <h5 class="text-center">Hasil Hash:</h5>
                        <p class="text-center text-muted small">Salin teks di bawah ini dan tempelkan ke kolom `password` di database Anda.</p>
                        <div class="result-box">
                            <?php echo htmlspecialchars($hashed_password); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
