<?php
require_once '../../config/database.php';
include '../layouts/header.php';

// --- 1. LOGIK FILTER (Bulan & Tahun) ---
// Ambil bulan & tahun dari URL, kalau tidak ada pakai bulan/tahun sekarang
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Array Nama Bulan untuk Dropdown
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

try {
    // --- 2. QUERY TOTAL DENDA PER PERIODE ---
    // Mengambil total denda dari tabel Pengembalian yang terjadi di bulan/tahun dipilih
    $sql_denda = "SELECT SUM(denda_keterlambatan) 
                  FROM Pengembalian 
                  WHERE EXTRACT(MONTH FROM tanggal_kembali) = :bln 
                  AND EXTRACT(YEAR FROM tanggal_kembali) = :thn";
    $stmt = $pdo->prepare($sql_denda);
    $stmt->execute([':bln' => $bulan_pilih, ':thn' => $tahun_pilih]);
    $total_denda_periode = $stmt->fetchColumn() ?: 0; // Kalau null, jadikan 0

    // --- 3. QUERY BUKU TERPOPULER (Top 5) ---
    // Menghitung buku mana yang paling sering dipinjam di periode ini
    $sql_top = "SELECT b.judul, COUNT(p.id_peminjaman) as total_pinjam
                FROM Peminjaman p
                JOIN Buku b ON p.id_buku = b.id_buku
                WHERE EXTRACT(MONTH FROM p.tanggal_pinjam) = :bln 
                AND EXTRACT(YEAR FROM p.tanggal_pinjam) = :thn
                GROUP BY b.judul
                ORDER BY total_pinjam DESC
                LIMIT 5";
    $stmt = $pdo->prepare($sql_top);
    $stmt->execute([':bln' => $bulan_pilih, ':thn' => $tahun_pilih]);
    $top_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 4. QUERY REKAPITULASI PEMINJAMAN LENGKAP ---
    $sql_rekap = "SELECT p.*, a.nama_lengkap, b.judul, pg.tanggal_kembali, pg.denda_keterlambatan
                  FROM Peminjaman p
                  JOIN Anggota a ON p.id_anggota = a.id_anggota
                  JOIN Buku b ON p.id_buku = b.id_buku
                  LEFT JOIN Pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
                  WHERE EXTRACT(MONTH FROM p.tanggal_pinjam) = :bln 
                  AND EXTRACT(YEAR FROM p.tanggal_pinjam) = :thn
                  ORDER BY p.tanggal_pinjam DESC";
    $stmt = $pdo->prepare($sql_rekap);
    $stmt->execute([':bln' => $bulan_pilih, ':thn' => $tahun_pilih]);
    $rekap_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Terjadi kesalahan database: " . $e->getMessage();
}
?>

<style>
/* Hover effect untuk tombol Tampilkan - Pink Theme */
.btn-tampilkan {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    transition: all 0.3s ease;
}

.btn-tampilkan:hover {
    background-color: #d6669a !important; /* Pink lebih gelap */
    border-color: #d6669a !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 119, 167, 0.4);
}

/* Focus state untuk select dropdown */
.form-select:focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(231, 119, 167, 0.25) !important;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-file-invoice-dollar me-2"></i>Laporan Sirkulasi & Denda</h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pilih Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php foreach($nama_bulan as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Pilih Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun_pilih) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-tampilkan w-100">
                        <i class="fas fa-filter me-2"></i>Tampilkan
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-2"></i>Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Total Peminjaman</h6>
                    <h2 class="fw-bold text-primary"><?= count($rekap_data) ?></h2>
                    <small>Periode: <?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-danger h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted text-uppercase mb-2">Total Denda Masuk</h6>
                    <h2 class="fw-bold text-danger">Rp <?= number_format($total_denda_periode, 0, ',', '.') ?></h2>
                    <small>Pendapatan denda bulan ini</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="m-0 small"><i class="fas fa-trophy me-2"></i>Top Buku Bulan Ini</h6>
                </div>
                <ul class="list-group list-group-flush small">
                    <?php if(count($top_books) > 0): ?>
                        <?php foreach($top_books as $tb): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($tb['judul']) ?>
                                <span class="badge bg-primary rounded-pill"><?= $tb['total_pinjam'] ?>x</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted text-center">Belum ada peminjaman</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-dark">📄 Rincian Sirkulasi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Pinjam</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Status Kembali</th>
                            <th class="text-end">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($rekap_data) > 0): ?>
                            <?php foreach($rekap_data as $row): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['jatuh_tempo'])) ?></td>
                                <td class="text-center">
                                    <?php if($row['tanggal_kembali']): ?>
                                        <span class="badge bg-success">Kembali: <?= date('d/m/y', strtotime($row['tanggal_kembali'])) ?></span>
                                    <?php else: ?>
                                        <?php if(date('Y-m-d') > $row['jatuh_tempo']): ?>
                                            <span class="badge bg-danger">Terlambat</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Dipinjam</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?php if($row['denda_keterlambatan'] > 0): ?>
                                        <span class="text-danger">Rp <?= number_format($row['denda_keterlambatan'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada data transaksi pada periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>