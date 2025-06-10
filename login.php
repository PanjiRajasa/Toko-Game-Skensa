<?php
//session_start();  // Jangan lupa start session!

require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && md5($password) == $user['password']) {
        // Cek level admin
        if ($user['level'] === 'admin') {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_level'] = $user['level'];

            header("location: admin.php");  // arahkan ke halaman admin
            exit;
        } else {
            // Jika user tapi bukan admin
             $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_level'] = $user['level'];
            echo '<script>alert("Sukses Login"); window.location.href="homepage.php"</script>';
        }
    } else {
        echo '<script>alert("Wrong credentials")</script>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <!-- Hubungkan ke file CSS utama -->
  <link rel="stylesheet" href="Login.css"/>
</head>
<body>
  <!-- Wrapper utama untuk dua panel (gambar & form) -->
  <div class="container">

    <!-- PANEL KIRI: Menampilkan gambar cover/artwork -->
    <div class="panel image-panel">
      <!-- Ganti 'login-image.jpg' sesuai nama file gambarmu -->
      <img src="image/Togame.jpg" alt="Artwork" />
    </div>

    <!-- PANEL KANAN: Form login -->
    <div class="panel form-panel">
      <!-- Tombol kembali (arrow kiri) -->
      <a href="./landing_page.php" class="back">&larr;</a>

      <!-- Judul utama halaman -->
      <h1>Login</h1>

      <!-- Subjudul dengan instruksi singkat -->
      <p class="subtitle">
        Untuk Melanjutkan Website ini,
        Tolong <a href="#">Login</a> Terlebih Dahulu!
      </p>

      <!-- FORM LOGIN -->
      <form method="post">
        <!-- Input Email -->
        <input
          name="email"
          id="email-input"
          type="email"
          placeholder="Email"
          required
        />
        <!-- Input Password -->
        <input
          name="password"
          id="pw-input"
          type="password"
          placeholder="Kata Sandi"
          required
        />

        <!-- Baris link: Sign Up & Forgot Password -->
        <div class="links-row">
          <!-- Link pendaftaran akun baru -->
          <span>
            Belom Mempunyai Akun?
            <a href="register.php">Daftar</a>
          </span>
          <!-- Link lupa password -->
          <a href="./ganti_pw.php" class="forgot">Lupa Kata Sandi?</a>
        </div>

        <!-- Tombol kirim form -->
        <button type="submit">Masuk</button>
      </form>

    </div>
  </div>
  <script src="Login.js"></script>
</body>
</html>