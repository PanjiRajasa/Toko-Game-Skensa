<?php
session_start();
require 'config.php'; // pastikan file ini mendefinisikan $pdo dengan koneksi PDO

// ==== Handle Bersihkan Keranjang ====
if (isset($_POST['clear'])) {
    unset($_SESSION['cart']); // Atau bisa juga $_SESSION['cart'] = [];
    header("Location: ./homepage.php"); // Redirect untuk mencegah resubmit
    exit;
}

// ==== Proses Menampilkan Isi Keranjang ====
$cart = $_SESSION['cart'] ?? [];

$games_in_cart = [];
$total_price = 0;

if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $sql = "SELECT * FROM game WHERE ID IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_keys($cart));
    $fetched_games = $stmt->fetchAll();

    foreach ($fetched_games as $game) {
        $game['quantity'] = $cart[$game['ID']];
        $game['subtotal'] = $game['price'] * $game['quantity'];
        $total_price += $game['subtotal'];
        $games_in_cart[] = $game;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Keranjang - Togame Store</title>
  <link rel="stylesheet" href="./style/Keranjang.css" />
</head>
<body>
<!-- HEADER -->
<header>
  <div class="container">
    <div class="logo">
      <img src="image/Togame.jpg" alt="Togame Store Logo" onclick="window.location.href='./homepage.php' " style='cursor:pointer;'/>
    </div>
    <nav>
      <ul>
        <li><a href="./homepage.php">Beranda</a></li>
        <li><a href="keranjang.php" class="active">Keranjang</a></li>
        <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
        <li><a href="./profil.php">Profil</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="container cart">
  <div class="cart-list">
    <h2>Keranjang Kamu</h2>

    <?php if (empty($games_in_cart)): ?>
      <p>Keranjangmu kosong.</p>
    <?php else: ?>
      <?php foreach ($games_in_cart as $game): ?>
        <div class="item">
          <img src="<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['name']) ?>" />
          <div class="item-info">
            <h3><?= htmlspecialchars($game['name']) ?></h3>
            <p>Rp<?= number_format($game['price'], 0, ',', '.') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <aside class="summary">
    <h3>Ringkasan Harga</h3>
    <div class="line"></div>
    <div class="row"><span>Total Harga Game</span><span>Rp<?= number_format($total_price, 0, ',', '.') ?></span></div>
    <div class="line"></div>
    <div class="row total"><strong>Order Total</strong><strong>Rp<?= number_format($total_price, 0, ',', '.') ?></strong></div>

    <form method="POST" action="./scanQr.php">
      <button type="submit" name="checkout">Bayar</button>
    </form>

    <form method="POST" action="./keranjang.php">
      <button type="submit" name="clear">Bersihkan Keranjang</button>
    </form>
  </aside>
</main>

<!-- FOOTER -->
<footer>
  <h1>
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
