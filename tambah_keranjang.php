<?php
session_start();

if (!isset($_POST['game_id'])) {
    header("Location: homepage.php");
    exit();
}

$game_id = intval($_POST['game_id']);

// Simpan game_id ke keranjang session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Kalau sudah ada di keranjang, jumlahkan kuantitasnya (opsional)
if (isset($_SESSION['cart'][$game_id])) {
    $_SESSION['cart'][$game_id]++;
} else {
    $_SESSION['cart'][$game_id] = 1;
}

// Setelah ditambahkan, redirect ke halaman keranjang
header("Location: keranjang.php");
exit();
?>
