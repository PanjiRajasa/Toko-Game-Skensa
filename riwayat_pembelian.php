<?php
require 'config.php'; // Pastikan file ini mengatur $pdo sebagai instance PDO

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Ambil data pembelian user dari tabel checkout + game
$sql = "
    SELECT c.ID, c.price, c.date_checkout, g.image, g.name, u.name as username, u.email 

    FROM checkout as c
    INNER JOIN user as u ON c.user_id = u.ID
    INNER JOIN game as g ON c.game_id = g.ID

    WHERE c.user_id = ?
    ORDER BY c.date_checkout DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Pembelian</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./Riwayatpembelian.css">
</head>
<body>

<!-- HEADER -->
<header>
    <div class="container">
      <div class="logo">
          <img src="image/Togame.jpg" alt="Togame Store Logo" onclick="window.location.href='./homepage.php' " />
      </div>
      <nav>
          <ul>
          <li><a href="./homepage.php">Beranda</a></li>
          <li><a href="keranjang.php">Keranjang</a></li>
          <li><a href="riwayat_pembelian.php" class="active">Riwayat Pembelian</a></li>
          <li><a href="./profil.php">Profil</a></li>
          </ul>
      </nav>
    </div>
</header>

<main class="container history">
  <h2>Riwayat Pembelian</h2>

  <?php if (empty($orders)): ?>
    <p>Kamu belum melakukan pembelian.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>

      <section class="order-card">
        <h3><?= $order['username'] ?></h3>
        <h4><?= $order['email'] ?></h4>
        <br/>

        <div class="details">

        <!-- Gambar -->
          <div class="left">
            <img src="<?= htmlspecialchars($order['image']) ?>" alt="<?= htmlspecialchars($order['name']) ?>" width="100">
          </div>
        
          <!-- Nama game -->
          <div class="center">
            <p><strong>Game:</strong> <?= htmlspecialchars($order['name']) ?></p>
            <p><strong>Tanggal:</strong> <?= date("d M Y, H:i", strtotime($order['date_checkout'])) ?></p>
          </div>

          <!-- Harga -->
          <div class="right">
            <p><strong>Harga:</strong> Rp<?= number_format($order['price'], 0, ',', '.') ?></p>
            <p><strong>Total:</strong> <span class="bold">Rp<?= number_format($order['price'] * 1.1, 0, ',', '.') ?></span></p>
            <small style="color: gray;">*termasuk 10% pajak</small>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

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
