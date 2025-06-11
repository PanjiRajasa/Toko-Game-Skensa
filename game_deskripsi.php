<?php
session_start();
require 'config.php'; // Ini sudah mendefinisikan $pdo

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID game dari URL dan validasi
$game_id = $_GET['id'] ?? null;
if (!$game_id || !is_numeric($game_id)) {
    header("Location: homepage.php");
    exit();
}

// Ambil data game berdasarkan ID (gunakan prepared statement)
$sql_game = "SELECT * FROM game WHERE ID = ?";
$stmt = $pdo->prepare($sql_game);
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    echo "Game tidak ditemukan.";
    exit();
}

// Ambil ID user dari session
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Detail Game - <?= htmlspecialchars($game['name']) ?></title>
  <link rel="stylesheet" href="./game-deskripsi.css" />
</head>
<body>
  <!-- HEADER -->
<header>
  <div class="container">
    <div class="logo">
      <img src="image/Togame.jpg" alt="Togame Store Logo" />
    </div>
    <nav>
      <ul>
        <li><a href="./homepage.php" class="active">Beranda</a></li>
        <li><a href="keranjang.php">Keranjang</a></li>
        <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
        <li><a href="./profil.php">Profil</a></li>
      </ul>
    </nav>
  </div>
</header>

  <main class="detail-card">
    <img src="<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['name']) ?>" class="cover">
    <div class="info">
      <h1 class="title"><?= htmlspecialchars($game['name']) ?></h1>
      <p class="price">Rp<?= number_format($game['price'], 0, ',', '.') ?></p>
      <p class="rating">Rating Usia: <b><?= htmlspecialchars($game['rating']) ?>+</b></p>
      <p class="description">Deskripsi:<br/><?= nl2br(htmlspecialchars($game['simple_description'])) ?></p>

      <!-- Tambah keranjang button -->
      <form method="POST" action="tambah_keranjang.php">
        <input type="hidden" name="game_id" value="<?= $game['ID'] ?>">
        <button type="submit" class="btn add-to-cart">Tambahkan ke Keranjang</button>
      </form>
    </div>
  </main>
</body>
</html>
