<?php
session_start();
include 'koneksi.php';

$errors = [];
$success = '';

function generateCaptcha($length = 6) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    return substr(str_shuffle($chars), 0, $length);
}

// Buat CAPTCHA baru jika belum ada
if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = generateCaptcha();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['konfirmasi_password'];
    $captcha_input = $_POST['captcha'];

    // Validasi input
    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($confirm) || empty($captcha_input)) {
        $errors[] = "Semua field harus diisi.";
    } elseif ($captcha_input !== $_SESSION['captcha_code']) {
        $errors[] = "Captcha tidak sesuai.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid. Harus mengandung '@' dan domain.";
    } elseif ($password !== $confirm) {
        $errors[] = "Password dan konfirmasi tidak cocok.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password harus mengandung huruf besar, angka, dan karakter spesial.";
    } else {
        // Cek username sudah ada
        $stmt = $koneksi->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Username sudah digunakan.";
        } else {
            $hash = $password; // TANPA hashing, sesuai permintaan
            $role = 'user';
            $stmt = $koneksi->prepare("INSERT INTO user (nama_lengkap, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $username, $email, $hash, $role);

            if ($stmt->execute()) {
                $_SESSION['captcha_code'] = generateCaptcha(); // ganti captcha
                $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Buan Nongki</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div id="page">
    <nav class="main-nav" style="position: sticky; top: 0; z-index: 999; background-color: #fff;">
        <div class="container">
            <div class="logo-wrapper text-center">
                <img src="img/logo-icon.png" alt="Logo Icon" class="logo-icon">
                <img src="img/logo-text2.png" alt="Logo Text" class="logo-text">
            </div>
        </div>
    </nav>

    <section class="section-content">
        <div class="container" style="max-width: 600px; margin: auto;">
            <h2 class="text-center">Registrasi Pengguna</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="color: red;">
                    <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required style="width: 100%; padding: 10px;">
                </div>
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required style="width: 100%; padding: 10px;">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required style="width: 100%; padding: 10px;">
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" required style="width: 100%; padding: 10px;">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Konfirmasi Password</label>
                    <div style="position: relative;">
                        <input type="password" name="konfirmasi_password" id="confirm_password" class="form-control" required style="width: 100%; padding: 10px;">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                    </div>
                </div>

                <div class="mb-3" style="font-size: 14px; margin-top: 5px; margin-bottom: 0;">
                    <p style="margin-bottom: 5px;">Syarat Password:</p>
                    <ul style="margin: 0 0 10px 18px; padding: 0;">
                        <li id="uppercase" style="color:red;">❌ 1 huruf kapital</li>
                        <li id="number" style="color:red;">❌ 1 angka</li>
                        <li id="symbol" style="color:red;">❌ 1 karakter spesial</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <label>Captcha</label><br>
                    <strong style="background:#eee;padding:10px;letter-spacing:2px;display:inline-block;"><?php echo $_SESSION['captcha_code']; ?></strong><br>
                    <input type="text" name="captcha" class="form-control" placeholder="Masukkan kode di atas" required style="width: 100%; padding: 10px; margin-top: 10px; margin-bottom: 20px;">
                </div>

                <button type="submit" class="btn btn-brown mb-3">Daftar</button>
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
    function togglePassword(fieldId, icon) {
        const field = document.getElementById(fieldId);
        const type = field.getAttribute("type") === "password" ? "text" : "password";
        field.setAttribute("type", type);
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    }

    const passwordField = document.getElementById("password");
    passwordField.addEventListener("input", function () {
        const value = passwordField.value;
        document.getElementById("uppercase").style.color = /[A-Z]/.test(value) ? "green" : "red";
        document.getElementById("uppercase").textContent = /[A-Z]/.test(value) ? "✅ 1 huruf kapital" : "❌ 1 huruf kapital";

        document.getElementById("number").style.color = /\d/.test(value) ? "green" : "red";
        document.getElementById("number").textContent = /\d/.test(value) ? "✅ 1 angka" : "❌ 1 angka";

        document.getElementById("symbol").style.color = /[^A-Za-z0-9]/.test(value) ? "green" : "red";
        document.getElementById("symbol").textContent = /[^A-Za-z0-9]/.test(value) ? "✅ 1 karakter spesial" : "❌ 1 karakter spesial";
    });
</script>
</body>
</html>
