<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ganti Kata Sandi</title>
  <!-- Hubungkan ke file CSS utama -->
  <link rel="stylesheet" href="../style/Login.css"/>
</head>
<body>
    <!-- Wrapper utama untuk dua panel (gambar & form) -->
  <div class="container">

    <!-- PANEL KIRI: Menampilkan gambar cover/artwork -->
    <div class="panel image-panel">
      <!-- Ganti 'login-image.jpg' sesuai nama file gambarmu -->
      <img src="../image/Togame.jpg" alt="Artwork" />
    </div>

    <!-- PANEL KANAN: Form login -->
    <div class="panel form-panel">
      <!-- Tombol kembali (arrow kiri) -->
      <a href="../login.php" class="back">&larr;</a>

      <!-- Judul utama halaman -->
      <h1>Ganti Kata Sandi</h1>

      <!-- Subjudul dengan instruksi singkat -->
      <p class="subtitle">
        Masukkan Email untuk Menerima Link Reset Password
      </p>

      <!-- FORM LOGIN -->
      <form method="get" action="./send-password-reset.php">
        <!-- Input Email -->
        <input
          name="email"
          id="email-input"
          type="email"
          placeholder="Email"
          required
        />

        <!-- Input Password
        <input
          name="password"
          id="pw-input"
          type="password"
          placeholder="Kata Sandi"
          required
        /> -->


        <div class="links-row">
          <!-- Link pendaftaran akun baru -->
          <span>
            Belum Mempunyai Akun?
            <a href="register.php">Daftar</a>
          </span>
        </div>

        <!-- Tombol kirim form -->
        <button type="submit">Kirim</button>
      </form>

    </div>
  </div>
</body>
</html>