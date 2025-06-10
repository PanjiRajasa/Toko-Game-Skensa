<?php
// Koneksi ke database
session_start();
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");

// Cek koneksi
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Ambil data dari tabel game
$sql = "SELECT ID, name, price, image, simple_description, rating FROM game";
$result = mysqli_query($conn, $sql);

$user_id = $_SESSION['user_id'];
$sql2 = "SELECT * FROM user WHERE ID = $user_id";
$result2 = mysqli_query($conn, $sql2);

if ($result2 && mysqli_num_rows($result2) > 0) {
    $user = mysqli_fetch_assoc($result2); // ✅ Ambil data jadi array asosiatif
} else {
    $user = ['name' => 'Pengguna']; // fallback jika tidak ditemukan
}

$games = [];
while ($row = mysqli_fetch_assoc($result)) {
  $games[] = $row;
  // echo $row;
}

//10 item pertama
$sepuluhgames = array_slice($games, 0, 10);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Togame Store</title>
  <link rel="stylesheet" href="Beranda.css" />
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
          <li><a href="./homepage.php" class="active">Beranda</a></li>
          <li><a href="keranjang.php">Keranjang</a></li>
          <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
          <li><a href="./profil.php">Profil</a></li>
        </ul>
      </nav>
    </div>
  </header>


  <!-- HERO -->
  <section class="hero">
    <div class="container">
      <h1>Selamat datang <span class="gradient-text"><?= htmlspecialchars($user["name"]) ?>!</span></h1>
      <p>Siap untuk membeli game? 😊</p>
      <a href="#rekomendasi" class="btn">AYO BELI GAME!</a>
    </div>
  </section>

  <!-- REKOMENDASI -->
  <section id="rekomendasi" class="rekomendasi">
    <div class="container">
      <h2>Game Rekomendasi</h2>
      <p class="subtitle">Game rekomendasi untuk kamu yang gamer sejati</p>

      <div class="card-grid">
        <?php foreach ($sepuluhgames as $row): ?>
          <a href="game_deskripsi.php?id=<?= $row['ID'] ?>" class="card">
            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
            <div class="info">
              <h3><?= htmlspecialchars($row['name']) ?></h3>
              <p>Rp<?= number_format($row['price'], 0, ',', '.') ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- LIST GAME -->
  <section class="list-game">
    <div class="container1">
      <h2>List Game Kami</h2>
      <p class="subtitle">Silahkan dipilih gamenya!</p>

      <!-- card container -->
      <div class="card-grid">
        <?php foreach ($games as $row): ?>
          <a href="game_deskripsi.php?id=<?= $row['ID'] ?>" class="card">
            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
            <div class="info">
              <h3><?= htmlspecialchars($row['name']) ?></h3>
              <p>Rp<?= number_format($row['price'], 0, ',', '.') ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <h1>
      <!-- 'To' putih, 'game' gradient, ' Store' putih, '!' kuning -->
      <span class="white-text">To</span><span class="gradient-text">game</span>
      <span class="white-text"> Store</span><span class="yellow-text">!</span>
    </h1>
    <div class="container footer-nav">
      <nav>
        <ul>
          <li><a href="#">Beranda</a></li>
          <li><a href="keranjang.php">Keranjang</a></li>
          <li><a href="riwayat_pembelian.php">Riwayat Pembelian</a></li>
        </ul>
      </nav>
      <p>&copy; 2025 Togame. Hak cipta dilindungi undang-undang.</p>
    </div>
  </footer>
</body>

</html>

<?php
mysqli_close($conn);
?>
