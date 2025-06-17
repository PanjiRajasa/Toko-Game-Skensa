<?php
    require "../config.php";

    //ambil data dari POST token
    $token = $_GET['token'] ?? null;

    if(!$token) die("Token isn't valid");

    $token_hash = hash("sha256", $token);

    $query = "SELECT * FROM user
              WHERE reset_token_hash = ?";

    $stmt = $pdo->prepare($query);

    //execute query
    $stmt->execute([$token_hash]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user == null) {
        die("Token not found 🗿!");
    }

    //ambil tanggal expired token
    $expiresDate = strtotime($user['reset_token_expires_at']);

    //cek apakah token sudah expired apa belum
    if($expiresDate <= time()) {
        die("Token has expired 😡!");
    } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="../style/reset-password-form.css"/>
</head>
<body>
    <div class="reset-container">
            <h1>Reset Your Password</h1>

        <form method="post" action="./process-reset.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>"/>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" />
            </div>
            
            <div class="form-group">
                <label for="password-confirmation">Confirm Password</label>
                <input type="password" name="password-confirmation" id="password-confirmation" />
            </div>

            <button type="submit">Send</button>
        </form>
    </div>
    
</body>
</html>