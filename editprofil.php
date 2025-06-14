<?php
session_start();
require "config.php"; // File ini harus membuat koneksi PDO dalam variabel $pdo

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
    $password = $_POST['password'] ?? '';

    try {
        // Jika password tidak kosong, update password juga
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Bisa ganti ke password_hash() di masa depan
            $sql = "
                UPDATE user 
                SET image = :photo,
                    name = :name,
                    username = :username,
                    email = :email,
                    description = :bio,
                    password = :password
                WHERE ID = :user_id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':photo'    => $photo,
                ':name'     => $name,
                ':username' => $username,
                ':email'    => $email,
                ':bio'      => $bio,
                ':password' => $hashed_password,
                ':user_id'  => $user_id
            ]);
        } else {
            // Tanpa update password
            $sql = "
                UPDATE user 
                SET image = :photo,
                    name = :name,
                    username = :username,
                    email = :email,
                    description = :bio
                WHERE ID = :user_id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':photo'    => $photo,
                ':name'     => $name,
                ':username' => $username,
                ':email'    => $email,
                ':bio'      => $bio,
                ':user_id'  => $user_id
            ]);
        }

        echo'<script>alert("Profil berhasil diperbarui."); window.location.href="profil.php"</script>';
    } catch (PDOException $e) {
        echo'<script>alert("Gagal memperbarui profil."); window.location.href="editprofil.php"</script>';
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Profil</title>
  <link rel="stylesheet" href="edit-profile.css">
</head>
<body>

<!-- HEADER -->
<header>
  <div class="container">
    <div class="logo">
        <img src="image/Togame.jpg" alt="Togame Store Logo" onclick="window.location.href='./homepage.php' " style='cursor: pointer;'/>
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
    <img src="<?= !empty($user['image']) ? htmlspecialchars($user['image']) : './image/avatar.jpg' ?>" alt="Avatar Profil" class="avatar-lg">

    

    <form action="editprofil.php" method="post">
      <label for="photo">Link Foto Profil</label>
      <input type="url" id="photo" name="photo" value="<?= htmlspecialchars($user['image']) ?>" placeholder="https://example.com/image.jpg">

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
