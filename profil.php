<?php
session_start();
require "config.php"; // Harus membuat $pdo sebagai koneksi PDO

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Jika form disubmit, lakukan update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $photo    = $_POST['photo'] ?? '';
    $name     = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $bio      = $_POST['bio'] ?? '';

    try {
        $stmt = $pdo->prepare("
            UPDATE user 
            SET image = :photo,
                name = :name,
                username = :username,
                email = :email,
                description = :bio
            WHERE ID = :user_id
        ");

        $stmt->execute([
            ':photo'    => $photo,
            ':name'     => $name,
            ':username' => $username,
            ':email'    => $email,
            ':bio'      => $bio,
            ':user_id'  => $user_id
        ]);

        $success = "Profil berhasil diperbarui.";
    } catch (PDOException $e) {
        $error = "Gagal memperbarui profil: " . $e->getMessage();
    }
}

// Ambil data user
try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE ID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User tidak ditemukan.");
    }
} catch (PDOException $e) {
    die("Gagal mengambil data user: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil</title>
  <link rel="stylesheet" href="./style/Profile.css">
</head>
<body>

<!-- KHUSUS UNTUK PROFILE, DIV YANG DIDESAIN MENCEGAH FOOTER NAIK (KONTEN PROFILE KEDIKITAN SOALNYA) -->
<div class="main-content-settingers">
    <!-- HEADER -->
    <header>
        <div class="container">
        <div class="logo">
            <img src="image/Togame.jpg" alt="Togame Store Logo" onclick="window.location.href='./homepage.php' " style='cursor:pointer;'/>
        </div>
        <nav>
            <ul>
              <li><a href="./homepage.php">Beranda</a></li>
              <li><a href="keranjang.php">Keranjang</a></li>
              <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
              <li><a href="./profil.php" class="active">Profil</a></li>
            </ul>
        </nav>
        </div>
    </header>

    <main>
      <section class="profile">
        <img src="<?= !empty($user['image']) ? htmlspecialchars($user['image']) : './image/avatar.jpg' ?>" alt="Avatar Profil" class="profile-img">

        <div class="profile-text">
          <h1><?= htmlspecialchars($user['name']) ?></h1>
          <p class="username"> <?= htmlspecialchars($user['username']) ?> </p>
          <button class="edit-btn" onclick="window.location.href='./editprofil.php'">EDIT PROFILE</button>
          <a href="logout.php" class="logout-btn">LOG OUT</a>
        </div>
      </section>

      <section class="bio">
        <p><?= htmlspecialchars($user['description']) ?></p>
      </section>
    </main>


  </div>

  <footer>
            <h1>
                <!-- 'T' putih, 'ogame' gradient, ' Store' putih, '!' kuning -->
                <span class="white-text">To</span><span class="gradient-text">game</span>
                <span class="white-text"> Store</span><span class="yellow-text">!</span>
            </h1>
            <nav class="footer-nav">
                <ul>
                    <li><a href="homepage.php">Beranda</a></li>
                    <li><a href="keranjang.php">Keranjang</a></li>
                    <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
                </ul>
            </nav>
            <p>&copy; 2025 Togame. Hak cipta dilindungi undang-undang.</p>
  </footer>
</body>
</html>