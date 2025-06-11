<?php
session_start(); // Tambahkan ini di awal untuk mengaktifkan session

$host = 'sql203.infinityfree.com';
$dbname = 'if0_39199280_database_toko_game';
$username = 'if0_39199280';
$password = 'FenrysRajasa12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>