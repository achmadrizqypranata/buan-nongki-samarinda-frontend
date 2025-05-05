<?php
session_start();
include 'koneksi.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Semua field wajib diisi.";
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // TANPA password_hash
            if ($password === $user['password']) {
                $_SESSION['sudah_login'] = true;
                $_SESSION['id_user'] = $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email']; // <-- Tambahkan baris ini

                header("Location: review.php");
                exit;
            } else {
                $errors[] = "Username atau Password salah.";
            }
        } else {
            $errors[] = "Username tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Buan Nongki</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div id="page">
    <!-- Navbar -->
    <nav class="main-nav">
        <div class="container">
            <div class="logo-wrapper text-center">
                <img src="img/logo-icon.png" alt="Logo Icon" class="logo-icon">
                <img src="img/logo-text2.png" alt="Logo Text" class="logo-text">
            </div>
        </div>
    </nav>

    <section class="section-content">
        <div class="container" style="max-width: 500px; margin: auto;">
            <h2 class="text-center">Login Pengguna</h2>

            <?php foreach ($errors as $e): ?>
                <p style="color: red; text-align: center; margin-bottom: 10px;"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>


            <form method="post" action="">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                        <i class="fas fa-eye toggle-password" id="togglePassword" style="position: absolute; right: -10px; top: 42%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-brown mb-3">Login</button>
                <p class="mt-2">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </form>
        </div>
    </section>

    <footer class="footer-section">
        <div class="footer-container">
            <p class="copyright">© 2025 Buan Nongki</p>
            <p class="tagline">Temukan Tempat Nongkrong Favoritmu!</p>
        </div>
    </footer>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>