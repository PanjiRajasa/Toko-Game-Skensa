<?php
include "register_process.php";

// Start session at the top of the file
// session_start();

// Check for success/error messages from redirect
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Page</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="./style/Register.css"/>
</head>
<body>
  <div class="container">
    
    <!-- PANEL KIRI (gambar) -->
    <div class="image-panel">
      <img src="./image/Togame.jpg" alt="Register Illustration">
    </div>

    <!-- PANEL KANAN (form register) -->
    <div class="form-panel">
      <a href="login.php" class="back">&larr;</a>
      <h1>Register</h1>
      <div class="subtitle">Already have an account? <a href="login.php">Login</a></div>

      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form action="register_process.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password2" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
      </form>
    </div>

  </div>
</body>
</html>