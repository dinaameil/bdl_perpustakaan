<?php

$host = "localhost";
$port = "5433";
$dbname = "db_perpustakaan";
$user = "postgres";
$password = "c";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
} catch (PDOException $e) {
    // Error handling koneksi
    die("Koneksi Gagal: " . $e->getMessage());
}
?>