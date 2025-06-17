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

    //cek kalau user ada (ga error)
    if ($user) {
        //verifikasi passowrd
        $password_valid = false;

        // Cek jika password disimpan sebagai MD5
        if (strlen($user['password']) === 32 && ctype_xdigit($user['password'])) {
            
            // Verifikasi MD5
            if (md5($password) === $user['password']) {
                $password_valid = true;

                // Migrasi ke Bcrypt
                $newHash = password_hash($password, PASSWORD_BCRYPT);

                // Update database dengan hash baru
                $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE ID = ?");
                $stmt->execute([$newHash, $user['ID']]);
            }

        } else {
            // Verifikasi Bcrypt
            $password_valid = password_verify($password, $user['password']);
        }

        //kalau password valid
    if($password_valid) {

        // Cek level admin
        if ($user['level'] === 'admin') {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_level'] = $user['level'];

            header("location: ./admin/admin.php");  // arahkan ke halaman admin
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
  <link rel="stylesheet" href="./style/Login.css"/>
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
      <a href="./index.php" class="back">&larr;</a>

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
            Belum Mempunyai Akun?
            <a href="register.php">Daftar</a>
          </span>
          <!-- Link lupa password -->
          <a href="./reset-password/forgot-password.php" class="forgot">Lupa Kata Sandi?</a>
        </div>

        <!-- Tombol kirim form -->
        <button type="submit">Masuk</button>
      </form>

    </div>
  </div>
  <script src="Login.js"></script>
</body>
</html>