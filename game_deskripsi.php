<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID game dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect ke halaman beranda jika tidak ada id
    header("Location: homepage.php");
    exit();
}

$game_id = intval($_GET['id']);

// Query data game berdasarkan ID
$sql_game = "SELECT * FROM game WHERE id = $game_id";
$result_game = mysqli_query($conn, $sql_game);

if (!$result_game || mysqli_num_rows($result_game) == 0) {
    echo "Game tidak ditemukan.";
    exit();
}

$game = mysqli_fetch_assoc($result_game);

// data dari login
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
      <p class="description">Deskripsi:<br/><?= htmlspecialchars($game['simple_description']) ?></p>

      <!-- Tambah keranjang button -->
     <form method="POST" action="tambah_keranjang.php">
  <input type="hidden" name="game_id" value="<?= $game['ID'] ?>">
  <button type="submit" class="btn add-to-cart">Tambahkan ke Keranjang</button>
</form>
    </div>
  </main>
</body>
</html>

<?php
mysqli_close($conn);
?>
