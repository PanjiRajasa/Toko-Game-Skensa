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

//data dari login
$user_id = $_SESSION['user_id'];

// Jika form disubmit, lakukan update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $photo = mysqli_real_escape_string($conn, $_POST['photo']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    $update_sql = "UPDATE user 
                   SET image='$photo', name='$name', username='$username', email='$email', description='$bio'
                   WHERE ID=$user_id";

    if (mysqli_query($conn, $update_sql)) {
        // Refresh data dari database
        $success = "Profil berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui profil: " . mysqli_error($conn);
    }
}

// Ambil data user setelah/atau sebelum update
$sql = "SELECT * FROM user WHERE ID = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil</title>
  <link rel="stylesheet" href="./Profile.css">
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
      <img src="<?= htmlspecialchars($user['image'] ?? 'avatar.png') ?>" alt="Avatar" class="profile-img">
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