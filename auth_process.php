<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Cari user berdasarkan username
    $sql = "SELECT * FROM users_admin WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Cek apakah user ada DAN password cocok
    // Catatan: Karena tadi insert dummy passwordnya polos ('admin123'), kita cek langsung.
    // Nanti kalau mau lebih aman pakai password_verify() dan hash.
    if ($user && $password === $user['password']) {
        
        // Login Sukses! Simpan data di sesi
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['logged_in'] = true;

        // Lempar ke Dashboard
        header("Location: index.php");
        exit;
    } else {
        // Login Gagal
        header("Location: login.php?error=Username atau Password Salah!");
        exit;
    }
} else {
    header("Location: login.php");
}
?>