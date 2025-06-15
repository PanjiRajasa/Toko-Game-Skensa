<?php
session_start();
require "config.php"; // Pastikan file ini membuat variabel $pdo untuk koneksi PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email         = trim($_POST['email'] ?? '');
    $old_password  = $_POST['old-password'] ?? '';
    $new_password  = $_POST['new-password'] ?? '';
    $confirm_pw    = $_POST['confirm-password'] ?? '';

    // Validasi dasar
    if (empty($email) || empty($old_password) || empty($new_password) || empty($confirm_pw)) {
        echo "<script>alert('Semua kolom wajib diisi.'); window.history.back();</script>";
        exit;
    }

    if ($new_password !== $confirm_pw) {
        echo "<script>alert('Konfirmasi password tidak cocok.'); window.history.back();</script>";
        exit;
    }

    try {
        // Ambil password lama
        $stmt = $pdo->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifikasi password lama
            if (password_verify($old_password, $user['password'])) {
                // Hash password baru
                $hashed_new_pw = password_hash($new_password, PASSWORD_BCRYPT);

                // Update password
                $update = $pdo->prepare("UPDATE user SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE email = ?");
                if ($update->execute([$hashed_new_pw, $email])) {
                    echo "<script>alert('Kata sandi berhasil diperbarui. Silakan login kembali.'); window.location.href = './login.php';</script>";
                    exit;
                } else {
                    echo "<script>alert('Gagal memperbarui password.'); window.history.back();</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Kata sandi lama salah.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Email tidak ditemukan.'); window.history.back();</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.history.back();</script>";
        exit;
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
    <link rel="stylesheet" href="./style/change-password.css">
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
