<?php
session_start();

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");

// Cek koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari POST dan sanitasi
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $old_pw = md5(mysqli_real_escape_string($conn, $_POST['old-password']));       // hash dengan md5
    $new_pw = md5(mysqli_real_escape_string($conn, $_POST['new-password']));       // hash dengan md5
    $confirm_pw = md5(mysqli_real_escape_string($conn, $_POST['confirm-password'])); // hash dengan md5

    // Validasi password baru dan konfirmasi (bandingkan yang sudah di md5)
    if ($new_pw !== $confirm_pw) {
        die("<script>alert('Konfirmasi password tidak cocok.'); window.history.back();</script>");
    }

    // Ambil data user
    $stmt = $conn->prepare("SELECT password FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika user ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password lama (bandingkan md5 hash)
        if ($old_pw === $user['password']) {

            // Update password dan timestamp
            $stmt_update = $conn->prepare("UPDATE user SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE email = ?");
            $stmt_update->bind_param("ss", $new_pw, $email);

            if ($stmt_update->execute()) {
                echo "<script>alert('Kata sandi berhasil diperbarui. Silakan login kembali.'); window.location.href = './login.php';</script>";
                exit();
            } else {
                die("Gagal update password: " . $stmt_update->error);
            }

        } else {
            die("<script>alert('Kata sandi lama salah.'); window.history.back();</script>");
        }
    } else {
        die("<script>alert('Email tidak ditemukan.'); window.history.back();</script>");
    }
}

?>


<!DOCTYPE html>
<html lang="id">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Kata Sandi</title>
    <!-- 1. Hubungkan CSS -->
    <link rel="stylesheet" href="change-password.css">
    </head>
    <body>


    <!-- 3. Konten Utama -->
    <main class="container">
        <!-- 3.1 Judul Halaman -->
        <h1>Ubah Kata Sandi</h1>
        <!-- 3.2 Subjudul / Instruksi -->
        <p class="subtitle">
        Minimal 8 karakter kombinasi huruf besar, huruf kecil, dan angka
        </p>

        <!-- 3.3 Form Ubah Password -->
        <form action="./ganti_pw.php" method="post" class="form">
        <!-- 3.3.1 Input: Kata Sandi Lama -->
        <label for="old-password">Email</label>
        <!-- input juga -->
        <input 
            type="email" 
            id="email" 
            name="email"
            required
        >


        <!-- seperti biasa form -->

        <!-- 3.3.1 Input: Kata Sandi Lama -->
        <label for="old-password">Kata Sandi Lama</label>
        <!-- input juga -->
        <input 
            type="password" 
            id="old-password" 
            name="old-password" 
            placeholder=""
            required
        >

        <!-- 3.3.2 Input: Kata Sandi Baru -->

        <!-- ini juga sama, input -->
        <label for="new-password">Kata Sandi Baru</label>
        <input 
            type="password" 
            id="new-password" 
            name="new-password" 
            placeholder=""
            minlength="8"
            required
        >

        <!-- 3.3.3 Input: Ulang Kata Sandi Baru -->
        <label for="confirm-password">Ulang Kata Sandi Baru</label>
        <!-- input -->
        <input 
            type="password" 
            id="confirm-password" 
            name="confirm-password" 
            placeholder=""
            minlength="8"
            required
        >

        <!-- 3.3.4 Tombol Aksi -->
        <div class="buttons">
            <!-- button -->
            <button type="submit" class="btn-save">SIMPAN</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='./login.php'">BATAL</button>
        </div>
        </form>
    </main>

    </body>
</html>
