<?php
require_once '../../config/database.php';
include '../layouts/header.php';

// --- QUERY STATISTIK SEDERHANA ---

// 1. Hitung Total Buku
$stmt = $pdo->query("SELECT COUNT(*) FROM Buku");
$total_buku = $stmt->fetchColumn();

// 2. Hitung Total Anggota
$stmt = $pdo->query("SELECT COUNT(*) FROM Anggota");
$total_anggota = $stmt->fetchColumn();

// 3. Hitung Peminjaman yang Sedang Aktif (Belum kembali)
// Logic: Cari data di Peminjaman yang ID-nya TIDAK ADA di tabel Pengembalian
$stmt = $pdo->query("SELECT COUNT(*) FROM Peminjaman p 
                     LEFT JOIN Pengembalian pg ON p.id_peminjaman = pg.id_peminjaman 
                     WHERE pg.id_pengembalian IS NULL");
$pinjam_aktif = $stmt->fetchColumn();

// 4. Hitung Total Denda Masuk (Rupiah)
$stmt = $pdo->query("SELECT SUM(denda_keterlambatan) FROM Pengembalian");
$total_denda = $stmt->fetchColumn();

// 5. Ambil 5 Transaksi Terakhir (Buat tabel mini)
$sql_recent = "SELECT p.*, a.nama_lengkap, b.judul 
               FROM Peminjaman p
               JOIN Anggota a ON p.id_anggota = a.id_anggota
               JOIN Buku b ON p.id_buku = b.id_buku
               ORDER BY p.tanggal_pinjam DESC LIMIT 5";
$recent = $pdo->query($sql_recent)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">🚀 Dashboard Overview</h3>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Total Buku</h6>
                        <h2 class="mb-0 fw-bold"><?= $total_buku ?></h2>
                    </div>
                    <i class="fas fa-book fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Anggota Terdaftar</h6>
                        <h2 class="mb-0 fw-bold"><?= $total_anggota ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-warning text-dark h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Sedang Dipinjam</h6>
                        <h2 class="mb-0 fw-bold"><?= $pinjam_aktif ?></h2>
                    </div>
                    <i class="fas fa-book-reader fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Total Denda</h6>
                        <h2 class="mb-0 fw-bold">Rp <?= number_format($total_denda, 0, ',', '.') ?></h2>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>5 Transaksi Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent as $r): ?>
                                <tr>
                                    <td class="px-4 fw-bold"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($r['judul']) ?></td>
                                    <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="../transaksi/peminjaman.php" class="text-decoration-none small fw-bold">Lihat Semua Transaksi &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"></i>Pintasan</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="../transaksi/tambah_peminjaman.php" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus-circle me-2"></i> Transaksi Peminjaman
                        </a>
                        <a href="../anggota/tambah_anggota.php" class="btn btn-outline-success text-start">
                            <i class="fas fa-user-plus me-2"></i> Daftar Anggota Baru
                        </a>
                        <a href="../buku/tambah_buku.php" class="btn btn-outline-info text-start">
                            <i class="fas fa-book me-2"></i> Tambah Buku Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../layouts/footer.php'; ?>