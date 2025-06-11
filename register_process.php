<?php
session_start();

include "config.php";

// Hanya jalankan jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username   = trim($_POST['username'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';

    // Validasi sederhana
    if (!$username || !$name || !$email || !$password || !$password2) {
        echo '<script>alert("Semua kolom wajib diisi."); window.location.href="register.php";</script>';
        exit;
    }

    if ($password !== $password2) {
        echo '<script>alert("Konfirmasi password tidak cocok."); window.location.href="register.php";</script>';
        exit;
    }

    // Cek apakah email sudah digunakan
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo '<script>alert("Email sudah terdaftar!"); window.location.href="register.php";</script>';
        exit;
    }

    // Hash password secara aman
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO user (username, name, email, password, level) VALUES (?, ?, ?, ?, 'user')");

    if ($stmt->execute([$username, $name, $email, $hashed_password])) {
        echo '<script>alert("Registrasi Sukses! Silahkan Login."); window.location.href="login.php";</script>';
        exit;
    } else {
        echo '<script>alert("Terjadi kesalahan saat registrasi."); window.location.href="register.php";</script>';
        exit;
    }
}
?>
