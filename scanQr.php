<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pastikan user login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Cek apakah keranjang tidak kosong
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: keranjang.php");
    exit;
}

// Ambil detail game untuk menghitung harga per item
$ids = implode(',', array_keys($cart));
$sql = "SELECT ID, price FROM game WHERE ID IN ($ids)";
$result = mysqli_query($conn, $sql);

$now = date('Y-m-d H:i:s');

while ($row = mysqli_fetch_assoc($result)) {
    $game_id = $row['ID'];
    $quantity = $cart[$game_id];
    $price = $row['price'] * $quantity;

    // Masukkan ke tabel checkout
    $insert = "INSERT INTO checkout (price, date_checkout, updated_at, game_ID, user_id) 
               VALUES ('$price', '$now', '$now', '$game_id', '$user_id')";
    mysqli_query($conn, $insert);
}

// Bersihkan keranjang
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR</title>
    <link rel="stylesheet" href="./qrcode.css"/>
</head>
<body>
  <h1 style="text-align:center; margin-bottom: 20px;">Silakan Scan QR untuk Pembayaran</h1>

  <div class="container">
    <div class="card">
      <img src="image/qrcode.png" alt="QR Code" class="qr-image">
      <h3>Pindai Untuk Melanjutkan</h3>
      <p>Chat admin untuk mendapatkan info lebih lanjut</p>
    </div>
    <a href="./riwayat_pembelian.php" class="btn">LANJUT</a>
  </div>
</body>

</body>
</html>
