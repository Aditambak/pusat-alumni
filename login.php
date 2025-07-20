<?php
session_start();


require_once 'config.php';


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: alumni_dashboard.php");
    }
    exit;
}

$error_message = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($conn) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error_message = "NIM/Username dan Password tidak boleh kosong.";
        } else {

            $sql = "SELECT id, username, password, 'admin' as role FROM admins WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    $error_message = "Password salah.";
                }
            } else {
                $sql = "SELECT id, nim, password, 'alumni' as role FROM alumni WHERE nim = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username); 
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                     if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['nim']; 
                        $_SESSION['role'] = $user['role'];

                        // Redirect ke dashboard alumni
                        header("Location: alumni_dashboard.php");
                        exit;
                    } else {
                        $error_message = "Password salah.";
                    }
                } else {
                    $error_message = "NIM/Username tidak ditemukan.";
                }
            }
            $stmt->close();
        }
        $conn->close();
    } else {
        $error_message = "Gagal terhubung ke database.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pusat Alumni Universitas Ma Chung</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-form-section {
            padding: 3rem 4rem;
        }
        .login-image-section {
            background: url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            border-top-right-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .btn-login {
            background-color: #0d6efd;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
        }
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .brand-logo {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .error-message {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="col-lg-10 col-xl-9 mx-auto">
            <div class="card login-card">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="login-form-section">
                            <div class="d-flex align-items-center mb-4">
                                <i class="fas fa-graduation-cap fa-2x me-2 text-primary"></i>
                                <h5 class="brand-logo mb-0">Universitas Ma Chung</h5>
                            </div>
                            <h2 class="fw-bold mb-2">Welcome Back, Alumni!</h2>
                            <p class="text-muted mb-4">Silakan masuk untuk melanjutkan.</p>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">NIM / Username</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your NIM or Username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                    </div>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                                </div>
                                <div class="text-center">
                                    <a href="#" class="text-decoration-none small">Forgot Password?</a>
                                    <p class="text-muted small mt-2">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6 d-none d-md-block login-image-section">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
