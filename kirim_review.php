<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['sudah_login']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['tempat_id'])) {
    echo "ID tempat tidak ditemukan.";
    exit;
}
$tempat_id = intval($_GET['tempat_id']);

$result = $koneksi->query("SELECT * FROM tempat_ngopi WHERE id = $tempat_id");
if ($result->num_rows === 0) {
    echo "Tempat tidak ditemukan.";
    exit;
}
$tempat = $result->fetch_assoc();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_user = $_SESSION['nama_lengkap'];
    $rating = intval($_POST['rating']);
    $ulasan = trim($_POST['ulasan']);

    if ($rating < 1 || $rating > 5 || empty($ulasan)) {
        $errors[] = "Rating harus antara 1–5 dan ulasan wajib diisi.";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO review_user (tempat_id, nama_user, foto_profil, rating, ulasan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $tempat_id, $nama_user, $foto_profil, $rating, $ulasan);
        if ($stmt->execute()) {
            $success = "Review berhasil ditambahkan!";
        } else {
            $errors[] = "Gagal menyimpan review.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beri Review - <?= htmlspecialchars($tempat['nama_tempat']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&family=Cormorant+Garamond:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div id="page">
    <!-- Navbar -->
    <nav class="main-nav" style="position: sticky; top: 0; z-index: 999; background-color: #fff;">
        <div class="container">
            <div class="logo-wrapper text-center">
                <img src="img/logo-icon.png" alt="Logo Icon" class="logo-icon">
                <img src="img/logo-text2.png" alt="Logo Text" class="logo-text">
            </div>
        </div>
    </nav>

    <section class="section-content">
        <div class="container" style="max-width: 600px; margin: 0 auto;">
            <h2 class="text-center">Beri Review untuk <strong><?= htmlspecialchars($tempat['nama_tempat']) ?></strong></h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 10px;">
                    <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" style="color: green; margin-bottom: 10px;"><?= $success ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="rating">Rating (1–5)</label>
                    <select name="rating" class="form-control" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
                        <option value="">Pilih Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>">&#9733; <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="ulasan">Ulasan Anda</label>
                    <textarea name="ulasan" rows="5" class="form-control" required style="width: 100%; padding: 10px; margin-bottom: 20px;"></textarea>
                </div>

                <button type="submit" style="background-color: #a67c52; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                    Kirim Review
                </button>
                <a href="review.php" style="background-color: #fff; color: #a67c52; padding: 10px 20px; border-radius: 6px; font-weight: bold; margin-left: 10px; text-decoration: none; border: 1px solid #a67c52;">
                    Kembali
                </a>
            </form>
        </div>
    </section>

    <footer class="footer-section">
        <div class="footer-container">
            <p class="copyright">© 2025 Achmad Rizqy Pranata</p>
            <p class="tagline">Buan Nongki Samarinda – Temukan Tempat Nongkrong Favoritmu!</p>
        </div>
    </footer>
</div>
</body>
</html>
