<?php
session_start();
require "config.php"; // File ini harus mendefinisikan variabel $pdo

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

try {
    // Ambil detail game untuk menghitung total harga
    $ids = implode(',', array_map('intval', array_keys($cart))); // Bersihkan ID agar hanya angka

    $sql = "SELECT ID, price FROM game WHERE ID IN ($ids)";
    $stmt = $pdo->query($sql);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $now = date('Y-m-d H:i:s');

    $insertSql = "INSERT INTO checkout (price, date_checkout, updated_at, game_ID, user_id)
                  VALUES (:price, :date_checkout, :updated_at, :game_ID, :user_id)";
    $insertStmt = $pdo->prepare($insertSql);

    foreach ($games as $game) {
        $game_id = $game['ID'];
        $quantity = $cart[$game_id];
        $price = $game['price'] * $quantity;

        // Eksekusi insert
        $insertStmt->execute([
            ':price' => $price,
            ':date_checkout' => $now,
            ':updated_at' => $now,
            ':game_ID' => $game_id,
            ':user_id' => $user_id
        ]);
    }

    // Bersihkan keranjang
    unset($_SESSION['cart']);
} catch (PDOException $e) {
    die("Terjadi kesalahan saat proses checkout: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR</title>
    <link rel="stylesheet" href="./style/qrcode.css"/>
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
