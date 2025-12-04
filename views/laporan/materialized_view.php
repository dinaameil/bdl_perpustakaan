<?php
require_once '../../config/database.php';
include '../layouts/header.php';

$success = null;
$refresh_time = null;

// Proses Refresh Materialized View
if(isset($_POST['refresh_mv'])) {
    try {
        $start_time = microtime(true);
        $pdo->exec("REFRESH MATERIALIZED VIEW mv_laporan_buku_per_penerbit");
        $end_time = microtime(true);
        $refresh_time = round(($end_time - $start_time) * 1000, 2); // dalam ms
        $success = "✅ Materialized View berhasil di-refresh!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Ambil data dari Materialized View
$start_query = microtime(true);
$data_mv = $pdo->query("SELECT * FROM mv_laporan_buku_per_penerbit ORDER BY \"Jumlah Judul\" DESC")->fetchAll(PDO::FETCH_ASSOC);
$end_query = microtime(true);
$query_time = round(($end_query - $start_query) * 1000, 2);

// Bandingkan dengan Query Biasa (tanpa MV)
$start_normal = microtime(true);
$data_normal = $pdo->query("
    SELECT 
        p.nama_penerbit,
        COUNT(b.id_buku) AS \"Jumlah Judul\",
        SUM(b.jumlah_stok) AS \"Total Stok Fisik\"
    FROM Buku b
    JOIN Penerbit p ON b.id_penerbit = p.id_penerbit
    GROUP BY p.nama_penerbit
    ORDER BY \"Jumlah Judul\" DESC
")->fetchAll(PDO::FETCH_ASSOC);
$end_normal = microtime(true);
$normal_query_time = round(($end_normal - $start_normal) * 1000, 2);

// Hitung persentase peningkatan performa
$improvement = round((($normal_query_time - $query_time) / $normal_query_time) * 100, 2);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">⚡ Materialized View Performance Demo</h3>

    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <span class="badge bg-success ms-2">Refresh time: <?= $refresh_time ?> ms</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Performance Comparison -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bolt fa-3x text-success mb-3"></i>
                    <h6 class="text-uppercase text-muted small">Materialized View Query</h6>
                    <h2 class="fw-bold text-success mb-1"><?= $query_time ?> ms</h2>
                    <small class="text-muted">Waktu eksekusi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-3x text-warning mb-3"></i>
                    <h6 class="text-uppercase text-muted small">Normal Query (JOIN)</h6>
                    <h2 class="fw-bold text-warning mb-1"><?= $normal_query_time ?> ms</h2>
                    <small class="text-muted">Waktu eksekusi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h6 class="text-uppercase text-muted small">Performance Improvement</h6>
                    <h2 class="fw-bold text-primary mb-1">
                        <?= $improvement > 0 ? '+' : '' ?><?= $improvement ?>%
                    </h2>
                    <small class="text-muted">Lebih cepat</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Laporan Buku per Penerbit</h5>
            <form method="POST" style="display:inline;">
                <button type="submit" name="refresh_mv" class="btn btn-warning btn-sm">
                    <i class="fas fa-sync-alt me-2"></i>Refresh Materialized View
                </button>
            </form>
        </div>
        <div class="card-body">
            <p class="text-muted">Data ini diambil dari Materialized View yang menyimpan hasil agregasi secara fisik.</p>
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Penerbit</th>
                            <th class="text-center">Jumlah Judul</th>
                            <th class="text-center">Total Stok Fisik</th>
                            <th class="text-center">Rata-rata Stok/Judul</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach($data_mv as $row): 
                            $avg = round($row['Total Stok Fisik'] / $row['Jumlah Judul'], 1);
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_penerbit']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6"><?= $row['Jumlah Judul'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success fs-6"><?= $row['Total Stok Fisik'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark fs-6"><?= $avg ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">TOTAL:</td>
                            <td class="text-center"><?= array_sum(array_column($data_mv, 'Jumlah Judul')) ?></td>
                            <td class="text-center"><?= array_sum(array_column($data_mv, 'Total Stok Fisik')) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Penjelasan Detail -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-info"><i class="fas fa-info-circle me-2"></i>Penjelasan Materialized View</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-success"><i class="fas fa-check-circle me-2"></i>Keuntungan</h6>
                            <ul class="small">
                                <li><strong>Performance Tinggi:</strong> Data sudah dihitung dan disimpan secara fisik, tidak perlu JOIN ulang.</li>
                                <li><strong>Cocok untuk Laporan:</strong> Data agregat yang jarang berubah seperti statistik dan dashboard.</li>
                                <li><strong>Mengurangi Load Database:</strong> Query kompleks tidak perlu dijalankan berkali-kali.</li>
                                <li><strong>Dapat Diindeks:</strong> Bisa ditambahkan index untuk performa lebih baik lagi.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Kekurangan</h6>
                            <ul class="small">
                                <li><strong>Data Tidak Real-time:</strong> Perlu di-refresh manual atau terjadwal untuk update data.</li>
                                <li><strong>Konsumsi Storage:</strong> Memakan ruang penyimpanan karena menyimpan hasil query.</li>
                                <li><strong>Maintenance:</strong> Perlu dijadwalkan refresh berkala (CRON job).</li>
                            </ul>
                            
                            <div class="alert alert-warning mt-3 mb-0">
                                <strong>⏰ Best Practice:</strong><br>
                                Jadwalkan refresh di jam-jam sepi (misal: tengah malam) atau trigger otomatis setelah transaksi tertentu.
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mt-3"><i class="fas fa-code me-2"></i>Query SQL yang Digunakan</h6>
                    <div class="bg-dark text-light p-3 rounded">
                        <code style="color: #fff;">
                            <span style="color: #ff6b6b;">CREATE MATERIALIZED VIEW</span> mv_laporan_buku_per_penerbit <span style="color: #ff6b6b;">AS</span><br>
                            <span style="color: #ff6b6b;">SELECT</span> <br>
                            &nbsp;&nbsp;p.nama_penerbit,<br>
                            &nbsp;&nbsp;<span style="color: #4ecdc4;">COUNT</span>(b.id_buku) <span style="color: #ff6b6b;">AS</span> "Jumlah Judul",<br>
                            &nbsp;&nbsp;<span style="color: #4ecdc4;">SUM</span>(b.jumlah_stok) <span style="color: #ff6b6b;">AS</span> "Total Stok Fisik"<br>
                            <span style="color: #ff6b6b;">FROM</span> Buku b<br>
                            <span style="color: #ff6b6b;">JOIN</span> Penerbit p <span style="color: #ff6b6b;">ON</span> b.id_penerbit = p.id_penerbit<br>
                            <span style="color: #ff6b6b;">GROUP BY</span> p.nama_penerbit;<br><br>

                            <span style="color: #95e1d3;">-- Cara refresh:</span><br>
                            <span style="color: #ff6b6b;">REFRESH MATERIALIZED VIEW</span> mv_laporan_buku_per_penerbit;
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>