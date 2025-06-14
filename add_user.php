<?php
require 'config.php';

if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $description = trim($_POST['description']);
    $level = ($_POST['level'] === 'admin') ? 'admin' : 'user';
    $password = $_POST['password'];
    $image = trim($_POST['image']);

    if (!$username || !$email || !$password) {
        $error = "Username, Email, dan Password wajib diisi!";
        echo '<script> alert("' . $error . '"); </script>';
    } else {
        // Cek apakah email sudah ada
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $check_stmt->execute([$email]);
        $email_count = $check_stmt->fetchColumn();

        if ($email_count > 0) {
            $error = "Email sudah terdaftar!";
            echo '<script> alert("' . $error . '"); </script>';
        } else {
            // Insert data
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO user (username, name, email, description, level, password, image, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$username, $name, $email, $description, $level, $password_hash, $image]);

            header("Location: admin.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna</title>
    <link rel="stylesheet" href="add_user.css">
</head>
<body>
        <h1>Tambah User Baru</h1>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:<br>
            <input type="text" name="username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        </label><br><br>

        <label>Name:<br>
            <input type="text" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </label><br><br>

        <label>Description:<br>
            <textarea name="description" rows="4" cols="40"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </label><br><br>

        <label>Level:<br>
            <select name="level">
                <option value="user" <?= (isset($_POST['level']) && $_POST['level'] === 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (isset($_POST['level']) && $_POST['level'] === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </label><br><br>

        <label>Password:<br>
            <input type="password" name="password" required>
        </label><br><br>

        <label>URL Image:<br>
            <input type="text" name="image" value="<?= isset($_POST['image']) ? htmlspecialchars($_POST['image']) : '' ?>" placeholder="https://example.com/image.jpg">
        </label><br><br>

        <button type="submit">Tambah User</button>
        <a href="admin.php">Batal</a>
    </form>
</body>
</html>



