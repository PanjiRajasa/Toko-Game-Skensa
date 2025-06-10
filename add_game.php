<?php
require 'config.php';

if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $image = trim($_POST['image']);
    $simple_description = trim($_POST['simple_description']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

    // Validasi sederhana
    if (!$name) {
        $error = "Nama game wajib diisi!";
    } elseif ($price < 0) {
        $error = "Harga harus bernilai 0 atau lebih!";
    } elseif ($rating !== null && ($rating < 0)) {
        $error = "Rating harus lebih dari!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO game (name, price, image, simple_description, rating, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $price, $image, $simple_description, $rating]);
        header("Location: admin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Game</title>
    <link rel="stylesheet" href="add_game.css">
</head>
<body>
    <h1>Tambah Game Baru</h1>
<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <label>Nama Game:<br>
        <input type="text" name="name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
    </label><br><br>

    <label>Harga:<br>
        <input type="number" name="price" min="0" required value="<?= isset($_POST['price']) ? (int)$_POST['price'] : '0' ?>">
    </label><br><br>

    <label>URL Image:<br>
        <input type="text" name="image" value="<?= isset($_POST['image']) ? htmlspecialchars($_POST['image']) : '' ?>" placeholder="https://example.com/image.jpg">
    </label><br><br>

    <label>Deskripsi Singkat:<br>
        <textarea name="simple_description" rows="4" cols="40"><?= isset($_POST['simple_description']) ? htmlspecialchars($_POST['simple_description']) : '' ?></textarea>
    </label><br><br>

    <label>Rating Usia:<br>
        <input type="number" name="rating" min="0" step="1" value="<?= isset($_POST['rating']) ? (int)$_POST['rating'] : '' ?>">
    </label><br><br>

    <button type="submit">Tambah Game</button>
    <a href="admin.php">Batal</a>
</form>
</body>
</html>
