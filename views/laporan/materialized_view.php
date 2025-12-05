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

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Laporan Buku per Penerbit</h5>
            <form method="POST" style="display:inline;">
                <button type="submit" name="refresh_mv" class="btn btn-warning btn-sm">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </form>
        </div>
        <div class="card-body">
            
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
</div>

<?php include '../layouts/footer.php'; ?>