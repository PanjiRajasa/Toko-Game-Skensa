<?php

// Database connection
$conn = mysqli_connect("localhost", "root", "", "database_toko_game");

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Only process if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = $_POST['password'];
  $password2 = $_POST['password2'];

  // Simple validation
  if ($password != $password2) {
    echo '<script>alert("Konfirmasi password tidak cocok."); window.location.href="register.php";</script>';
    exit(); 
  }

  // Check if email exists
  $check = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");
  if (mysqli_num_rows($check) > 0) {
    echo '<script>alert("Email sudah terdaftar!"); window.location.href="register.php";</script>';
    exit(); 
  }


  // Hash password (using simple md5 - not secure)
  $hashed_password = md5($password);

  // Insert user
  $sql = "INSERT INTO user (username, name, email, password, level) 
          VALUES ('$username', '$name', '$email', '$hashed_password', 'user')";

  if (mysqli_query($conn, $sql)) { 
    echo '<script>alert("Registrasi Sukses! Silahkan Login."); window.location.href="login.php";</script>';
    exit(); 
  } else {
    $_SESSION['error'] = "Registration failed: " . mysqli_error($conn);
    header("Location: register.php");
  }

  mysqli_close($conn);
  exit();
}
?>