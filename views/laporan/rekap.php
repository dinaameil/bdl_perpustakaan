<?php
require_once '../../config/database.php';

// --- BAGIAN 1: LOGIC REFRESH MATERIALIZED VIEW (Wajib ada!) ---
if (isset($_POST['refresh_mv'])) {
    try {
        // Perintah SQL khusus PostgreSQL untuk menyegarkan data view fisik
        $pdo->exec("REFRESH MATERIALIZED VIEW mv_statistik_pinjam");
        $success_msg = "Data Statistik Berhasil Diperbarui (Refreshed)!";
    } catch (PDOException $e) {
        $error_msg = "Gagal refresh view: " . $e->getMessage();
    }
}

// --- BAGIAN 2: QUERY DATA (Memanggil Fitur DB Lanjut) ---

// A. Panggil STORED FUNCTION: hitung_total_denda()
// Function ini sudah kita buat di SQL sebelumnya.
$stmt = $pdo->query("SELECT hitung_total_denda()"); 
$total_denda_live = $stmt->fetchColumn();

// B. Panggil COMPLEX VIEW: view_buku_lengkap
// Ini memenuhi syarat "View dengan multiple joins" untuk laporan
$sql_view = "SELECT * FROM view_buku_lengkap ORDER BY jumlah_stok ASC";
$data_buku = $pdo->query($sql_view)->fetchAll(PDO::FETCH_ASSOC);

// C. Panggil MATERIALIZED VIEW: mv_statistik_pinjam
// Ini data statis yang perlu di-refresh manual
$sql_mv = "SELECT * FROM mv_statistik_pinjam ORDER BY total_dipinjam DESC";
$data_statistik = $pdo->query($sql_mv)->fetchAll(PDO::FETCH_ASSOC);

include '../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-chart-line me-2"></i>Laporan & Analisis Data</h3>
        
        <form method="POST">
            <button type="submit" name="refresh_mv" class="btn btn-primary shadow-sm">
                <i class="fas fa-sync-alt me-2"></i>Refresh Data Statistik
            </button>
        </form>
    </div>

    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #FF6B6B 0%, #EE5253 100%); color: white;">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Total Pendapatan Denda</h6>
                    <h2 class="display-6 fw-bold mb-0">Rp <?= number_format($total_denda_live, 0, ',', '.') ?></h2>
                    <div class="mt-3 small" style="opacity: 0.9;">
                        <i class="fas fa-calculator me-1"></i> Dihitung via <b>Stored Function</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">📊 Statistik Kategori Terpopuler (Materialized View)</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Kategori Buku</th>
                                        <th class="text-center">Total Dipinjam</th>
                                        <th>Popularitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data_statistik as $stat): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($stat['nama_kategori']) ?></td>
                                        <td class="text-center fw-bold text-primary"><?= $stat['total_dipinjam'] ?>x</td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: <?= ($stat['total_dipinjam'] * 10) ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <small class="text-muted fst-italic">*Data ini berasal dari Materialized View. Klik tombol "Refresh" di atas untuk update data terbaru.</small>
                        </div>
                        <div class="col-md-5 text-center">
                             <i class="fas fa-chart-pie fa-6x text-muted opacity-25"></i>
                             <p class="mt-2 text-muted small">Visualisasi Data</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">📚 Laporan Detail Koleksi (Complex View)</h6>
            <span class="badge bg-info text-dark">Data Read-Only</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th>Kategori</th>
                            <th class="text-center">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_buku as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['judul']) ?></td>
                            <td><?= htmlspecialchars($b['pengarang']) ?></td>
                            <td><?= htmlspecialchars($b['nama_penerbit']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($b['nama_kategori']) ?></span></td>
                            <td class="text-center">
                                <?php if($b['jumlah_stok'] < 3): ?>
                                    <span class="badge bg-danger">Kritis: <?= $b['jumlah_stok'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $b['jumlah_stok'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../layouts/footer.php'; ?>