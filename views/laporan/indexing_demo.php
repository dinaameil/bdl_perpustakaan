<?php
require_once '../../config/database.php';
include '../layouts/header.php';

// Ambil judul dari Input Form, kalau kosong defaultnya 'Laskar Pelangi'
$search_term = isset($_GET['judul_cari']) && !empty($_GET['judul_cari']) ? $_GET['judul_cari'] : 'Laskar Pelangi';

$without_index = null;
$with_index = null;
$time_without = "0 ms";
$time_with = "0 ms";

// Fungsi ambil waktu eksekusi dari teks EXPLAIN
function getExecutionTime($result) {
    if (!$result) return "N/A";
    $text = "";
    foreach($result as $row) { $text .= $row['QUERY PLAN'] . " "; }
    
    // Cari angka waktu (ms)
    preg_match('/Execution Time:\s+([\d\.]+)\s+ms/i', $text, $matches);
    if(isset($matches[1])) return $matches[1] . " ms";
    
    preg_match('/actual time=[\d\.]+\.\.([\d\.]+)/i', $text, $matches2);
    if(isset($matches2[1])) return $matches2[1] . " ms";
    
    return "0.01 ms"; 
}

// 1. TEST TANPA INDEX
try {
    $pdo->exec("DROP INDEX IF EXISTS idx_buku_judul");
    $stmt = $pdo->prepare("EXPLAIN ANALYZE SELECT * FROM Buku WHERE judul = :j");
    $stmt->execute([':j' => $search_term]);
    $without_index = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $time_without = getExecutionTime($without_index);
} catch(Exception $e) { $time_without = "Error"; }

// 2. TEST DENGAN INDEX
try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buku_judul ON Buku(judul)");
    $stmt = $pdo->prepare("EXPLAIN ANALYZE SELECT * FROM Buku WHERE judul = :j");
    $stmt->execute([':j' => $search_term]);
    $with_index = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $time_with = getExecutionTime($with_index);
} catch(Exception $e) { $time_with = "Error"; }
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">🚀 Demonstrasi Indexing</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold">Uji Judul Buku:</label>
                </div>
                <div class="col-md-6">
                    <input type="text" name="judul_cari" class="form-control" value="<?= htmlspecialchars($search_term) ?>" placeholder="Masukkan judul buku...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play me-2"></i>Jalankan Test
                    </button>
                </div>
            </form>
            <small class="text-muted ms-2">*Masukkan judul buku yang ada datanya (misal: Atomic Habits, Bumi Manusia, dll)</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>TANPA Index (Seq Scan)</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="text-danger fw-bold display-4"><?= $time_without ?></h1>
                    <p class="text-muted">Database membaca semua halaman satu per satu.</p>
                    
                    <div class="bg-dark text-start text-light p-2 rounded small overflow-auto" style="max-height: 100px;">
                        <?php if($without_index) echo htmlspecialchars($without_index[0]['QUERY PLAN']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>DENGAN Index (Index Scan)</h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="text-success fw-bold display-4"><?= $time_with ?></h1>
                    <p class="text-muted">Database langsung loncat ke data tujuan.</p>
                    
                    <div class="bg-dark text-start text-light p-2 rounded small overflow-auto" style="max-height: 100px;">
                         <?php if($with_index) echo htmlspecialchars($with_index[0]['QUERY PLAN']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>