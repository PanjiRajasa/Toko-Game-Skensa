<?php
// Mulai sesi
session_start();

// Hapus semua data sesi
$_SESSION = array();

// Jika ada cookie sesi, hapus juga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Arahkan ke halaman login atau beranda
header("Location: login.php");
exit();
?>
