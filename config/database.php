<?php

$host = "localhost";
$port = "5434";
$dbname = "bd_perpustakaan";
$user = "postgres";
$password = "akuDina06";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
} catch (PDOException $e) {
    // Error handling koneksi
    die("Koneksi Gagal: " . $e->getMessage());
}
?>