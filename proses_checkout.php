<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'] ?? null;
$cart = $_SESSION['cart'] ?? [];

if (!$user_id || empty($cart)) {
    header("Location: keranjang.php");
    exit();
}

foreach ($cart as $game_id => $quantity) {
    // Ambil info game
    $game_query = mysqli_query($conn, "SELECT price FROM game WHERE ID = $game_id");
    $game = mysqli_fetch_assoc($game_query);
    if (!$game) continue;

    $total_price = $game['price'] * $quantity;

    // Simpan satu per satu ke tabel checkout
    for ($i = 0; $i < $quantity; $i++) {
        $stmt = mysqli_prepare($conn, "INSERT INTO checkout (price, game_ID, user_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iii', $game['price'], $game_id, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

// Bersihkan keranjang
unset($_SESSION['cart']);

header("Location: riwayat_pembelian.php");
exit();
?>
