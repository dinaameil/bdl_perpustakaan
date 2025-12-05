<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $base_url = "http://localhost/perpus_app/"; 
    header("Location: " . $base_url . "login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Perpustakaan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* --- 1. PALET WARNA --- */
        :root {
            --sidebar-bg: #00174dff;
            --sidebar-hover: #6ea1f4ff;
            --primary-color: #e777a7ff;
            --primary-hover: #a78ee1ff;
            --bg-light: #f8f9fa;
            --text-grey: #fefefeff;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            color: #333;
            overflow-x: hidden; /* Mencegah scroll ke samping */
        }

        /* --- 2. SIDEBAR FIXED (DIAM DI TEMPAT) --- */
        .sidebar { 
            position: fixed;        /* KUNCI UTAMA: Agar sidebar mengambang/tetap */
            top: 0; 
            left: 0;
            height: 100vh;          /* Tinggi full layar */
            width: 260px;           /* Lebar tetap */
            background-color: var(--sidebar-bg); 
            color: white; 
            overflow-y: auto;       /* Bisa discroll kalau menunya kepanjangan */
            z-index: 1000;          /* Pastikan di atas layer lain */
            transition: all 0.3s ease;
        }

        .sidebar .brand {
            padding: 25px 20px;
            font-weight: 700;
            font-size: 1.4rem;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar a { 
            color: var(--text-grey); 
            text-decoration: none; 
            padding: 14px 25px; 
            display: block; 
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover { 
            background: var(--sidebar-hover); 
            color: #fff; 
            padding-left: 30px; 
        }

        .sidebar a.active { 
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.1) 0%, transparent 100%); 
            color: #fff; 
            border-left: 4px solid var(--primary-color); 
        }

        .sidebar i { width: 25px; text-align: center; margin-right: 8px;}
        
        .menu-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #4690f8ff;
            margin: 25px 25px 10px;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        /* --- 3. WRAPPER KONTEN KANAN --- */
        .main-content {
            margin-left: 260px; /* Geser konten ke kanan sebesar lebar sidebar */
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- 4. NAVBAR STICKY (NEMPEL DI ATAS) --- */
        .navbar { 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.02); 
            border-bottom: 1px solid #eee;
            position: sticky;      /* Agar navbar nempel saat discroll */
            top: 0;
            z-index: 900;
        }

        .content { padding: 30px; flex-grow: 1; }
        
        .card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
            overflow: hidden;
        }
        .card-header.bg-primary {
            background: var(--primary-color) !important; 
            padding: 15px 20px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <i class="fas fa-book-open me-2 text-primary"></i> PERPUS<span style="color: var(--primary-color);">APP</span>
    </div>
    
    <nav class="mt-2 pb-5">
        <div class="menu-label">Menu Utama</div>
        <a href="../../index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <div class="menu-label">Master Data</div>
        <a href="../anggota/list_anggota.php" class="<?= strpos($_SERVER['PHP_SELF'], 'anggota') !== false ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Data Anggota
        </a>
        <a href="../buku/list_buku.php" class="<?= strpos($_SERVER['PHP_SELF'], 'buku') !== false ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Data Buku
        </a>
        
        <div class="menu-label">Sirkulasi</div>
        <a href="../transaksi/peminjaman.php" class="<?= strpos($_SERVER['PHP_SELF'], 'peminjaman.php') !== false ? 'active' : '' ?>">
            <i class="fas fa-hand-holding"></i> Peminjaman
        </a>
        <a href="../transaksi/pengembalian.php" class="<?= strpos($_SERVER['PHP_SELF'], 'pengembalian.php') !== false ? 'active' : '' ?>">
            <i class="fas fa-undo"></i> Pengembalian
        </a>
        
        <div class="menu-label">Laporan & Database</div>
        <a href="../laporan/sirkulasi.php" class="<?= basename($_SERVER['PHP_SELF']) == 'sirkulasi.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar"></i> Laporan Sirkulasi
        </a>
        <a href="../laporan/function_demo.php" class="<?= basename($_SERVER['PHP_SELF']) == 'function_demo.php' ? 'active' : '' ?>">
            <i class="fas fa-code"></i> Function Demo
        </a>
        <a href="../laporan/views_demo.php" class="<?= basename($_SERVER['PHP_SELF']) == 'views_demo.php' ? 'active' : '' ?>">
            <i class="fas fa-eye"></i> Views Demo
        </a>
        <a href="../laporan/materialized_view.php" class="<?= basename($_SERVER['PHP_SELF']) == 'materialized_view.php' ? 'active' : '' ?>">
            <i class="fas fa-database"></i> Materialized View
        </a>
        <a href="../laporan/indexing_demo.php" class="<?= basename($_SERVER['PHP_SELF']) == 'indexing_demo.php' ? 'active' : '' ?>">
            <i class="fas fa-bolt"></i> Indexing Demo
        </a>
        
        <div class="mt-5 px-4 pb-4">
            <a href="../../logout.php" class="btn btn-danger w-100 shadow-sm text-white" style="border-radius: 8px;">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </nav>
</div>

<div class="main-content">
    
    <nav class="navbar navbar-expand-lg py-3 px-4">
        <div class="container-fluid">
            <span class="navbar-text text-secondary fw-medium">
                <i class="fas fa-calendar-alt me-2"></i> <?= date('d F Y') ?>
            </span>
            <span class="navbar-text fw-bold text-dark">
                Halo, Administrator <img src="https://ui-avatars.com/api/?name=Admin&background=4361ee&color=fff&rounded=true" width="30" class="ms-2">
            </span>
        </div>
    </nav>

    <div class="content">