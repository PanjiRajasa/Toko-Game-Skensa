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

$user_id = $_SESSION['user_id'];

// Jika form disubmit, lakukan update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $photo = mysqli_real_escape_string($conn, $_POST['photo']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Bangun query update
    $update_sql = "UPDATE user 
                   SET image='$photo', name='$name', username='$username', email='$email', description='$bio'";

    // Jika password diisi, update juga
    if (!empty($password)) {
        $hashed_password = md5($password);
        $update_sql .= ", password='$hashed_password'";
    }

    $update_sql .= " WHERE ID=$user_id";

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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Profil</title>
  <link rel="stylesheet" href="edit-profile.css">
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
            <li><a href="./profil.php">Profil</a></li>
            </ul>
        </nav>
        </div>
    </header>

<main>
  <section class="form-container">
    <!-- Avatar -->
    <img src="<?= htmlspecialchars($user['image'] ?? 'avatar.png') ?>" alt="Avatar Profil" class="avatar-lg">

    <?php if (isset($success)) echo '<script>alert("'.$success.'"); window.location.href="./profil.php"</script>'; ?>
    <?php if (isset($error)) echo '<script>alert("'.$error.'"); window.location.href="./editprofil.php"</script>'; ?>

    <form action="editprofil.php" method="post">
      <label for="photo">Link Foto Profil</label>
      <input type="url" id="photo" name="photo" value="<?= htmlspecialchars($user['image']) ?>">

      <label for="name">Nama</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

      <label for="bio">Deskripsi</label>
      <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['description']) ?></textarea>

      <label for="password">Password (Biarkan kosong jika tidak ingin diubah)</label>
      <input type="password" id="password" name="password">

      <div class="buttons">
        <button type="button" class="btn" onclick="location.href='./profil.php'">BATAL</button>
        <button type="submit" class="btn">Simpan</button>
      </div>
    </form>
  </section>
</main>

</body>
</html>

<?php
mysqli_close($conn);
?>
