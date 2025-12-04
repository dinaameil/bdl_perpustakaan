<?php
require_once '../../config/database.php';
include '../layouts/header.php';

$without_index = null;
$with_index = null;
$search_term = 'Laskar Pelangi'; // Bisa diganti sesuai data Anda

// === TEST 1: Query TANPA Index ===
try {
    // Drop index dulu (kalau ada)
    $pdo->exec("DROP INDEX IF EXISTS idx_buku_judul");
    
    $start = microtime(true);
    $stmt = $pdo->query("EXPLAIN ANALYZE SELECT * FROM Buku WHERE judul = '$search_term'");
    $without_index = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $end = microtime(true);
    $time_without = round(($end - $start) * 1000, 4);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// === TEST 2: Query DENGAN Index ===
try {
    // Buat index
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_buku_judul ON Buku(judul)");
    
    $start = microtime(true);
    $stmt = $pdo->query("EXPLAIN ANALYZE SELECT * FROM Buku WHERE judul = '$search_term'");
    $with_index = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $end = microtime(true);
    $time_with = round(($end - $start) * 1000, 4);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Ekstrak execution time dari EXPLAIN ANALYZE
function extractExecutionTime($explain_result) {
    if(empty($explain_result)) return "N/A";
    
    $text = $explain_result[0]['QUERY PLAN'] ?? '';
    preg_match('/actual time=([\d.]+)\.\.([\d.]+)/', $text, $matches);
    
    if(isset($matches[2])) {
        return $matches[2] . ' ms';
    }
    
    preg_match('/Execution Time: ([\d.]+) ms/', $text, $matches2);
    if(isset($matches2[1])) {
        return $matches2[1] . ' ms';
    }
    
    return "N/A";
}

$exec_time_without = extractExecutionTime($without_index);
$exec_time_with = extractExecutionTime($with_index);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">🚀 Demonstrasi Database Indexing</h3>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Info Search Term -->
    <div class="alert alert-info">
        <strong><i class="fas fa-search me-2"></i>Search Term yang Diuji:</strong> 
        <code><?= htmlspecialchars($search_term) ?></code>
        <span class="ms-3 text-muted">| Query: <code>SELECT * FROM Buku WHERE judul = '<?= htmlspecialchars($search_term) ?>'</code></span>
    </div>

    <!-- Comparison Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-danger h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>TANPA Index (Sequential Scan)</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-hourglass-half fa-4x text-danger mb-3"></i>
                        <h2 class="text-danger fw-bold"><?= $exec_time_without ?></h2>
                        <p class="text-muted">Execution Time</p>
                    </div>
                    
                    <div class="alert alert-light border">
                        <strong>🔍 Metode Pencarian:</strong><br>
                        <span class="badge bg-danger">Sequential Scan (Seq Scan)</span>
                        <p class="small mb-0 mt-2">Database membaca <strong>SEMUA baris</strong> satu per satu dari awal sampai akhir untuk mencari data yang cocok.</p>
                    </div>

                    <h6 class="fw-bold mt-3">EXPLAIN ANALYZE Output:</h6>
                    <div class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-size: 0.85rem;">
                        <?php if($without_index): ?>
                            <?php foreach($without_index as $row): ?>
                                <div><?= htmlspecialchars($row['QUERY PLAN']) ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-success h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>DENGAN Index (Index Scan)</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-bolt fa-4x text-success mb-3"></i>
                        <h2 class="text-success fw-bold"><?= $exec_time_with ?></h2>
                        <p class="text-muted">Execution Time</p>
                    </div>
                    
                    <div class="alert alert-light border">
                        <strong>⚡ Metode Pencarian:</strong><br>
                        <span class="badge bg-success">Index Scan / Bitmap Index Scan</span>
                        <p class="small mb-0 mt-2">Database menggunakan <strong>B-Tree Index</strong> untuk langsung melompat ke lokasi data yang tepat. Jauh lebih cepat!</p>
                    </div>

                    <h6 class="fw-bold mt-3">EXPLAIN ANALYZE Output:</h6>
                    <div class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-size: 0.85rem;">
                        <?php if($with_index): ?>
                            <?php foreach($with_index as $row): ?>
                                <div><?= htmlspecialchars($row['QUERY PLAN']) ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Penjelasan Visual -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-primary"><i class="fas fa-graduation-cap me-2"></i>Penjelasan Cara Kerja Index</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger"><i class="fas fa-list me-2"></i>Tanpa Index (Sequential Scan)</h6>
                            <div class="bg-light p-3 rounded border">
                                <div class="mb-2">
                                    <span class="badge bg-secondary">Row 1</span> Bumi Manusia → <span class="text-muted">❌ Bukan</span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-secondary">Row 2</span> Negeri 5 Menara → <span class="text-muted">❌ Bukan</span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-secondary">Row 3</span> Laskar Pelangi → <span class="text-success">✅ KETEMU!</span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-secondary">Row 4</span> Atomic Habits → <span class="text-muted">❌ Bukan</span>
                                </div>
                                <div class="text-muted small">
                                    <em>...terus sampai row terakhir (meskipun sudah ketemu)</em>
                                </div>
                            </div>
                            <p class="text-danger small mt-2 mb-0">
                                <strong>⏱️ Lambat!</strong> Harus cek semua baris. Jika ada 10.000 buku, database cek 10.000 baris!
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-success"><i class="fas fa-bolt me-2"></i>Dengan Index (Index Scan)</h6>
                            <div class="bg-light p-3 rounded border">
                                <div class="mb-3">
                                    <strong>1. Database baca Index (B-Tree):</strong>
                                    <pre class="small mb-0 mt-1" style="background: #f8f9fa;">
A-L (→ Block 1)
├─ Atomic Habits
├─ Bumi Manusia
└─ <span class="text-success fw-bold">Laskar Pelangi</span> → Row #3
M-Z (→ Block 2)
                                    </pre>
                                </div>
                                <div>
                                    <strong>2. Langsung loncat ke Row #3:</strong><br>
                                    <span class="badge bg-success">Row 3</span> Laskar Pelangi → <span class="text-success">✅ KETEMU!</span>
                                </div>
                            </div>
                            <p class="text-success small mt-2 mb-0">
                                <strong>⚡ Cepat!</strong> Hanya baca index + 1 baris data. Tidak peduli ada 10.000 buku, tetap cepat!
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-tree fa-3x text-info mb-2"></i>
                                <h6 class="fw-bold">B-Tree Index</h6>
                                <p class="small text-muted">Struktur data seperti pohon yang memudahkan pencarian data secara hierarkis.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                                <h6 class="fw-bold">Kapan Pakai Index?</h6>
                                <p class="small text-muted">Kolom yang sering di-WHERE, JOIN, atau ORDER BY (seperti: judul, email, nomor_anggota)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-2"></i>
                                <h6 class="fw-bold">Trade-off</h6>
                                <p class="small text-muted">Index mempercepat SELECT tapi memperlambat INSERT/UPDATE karena index harus di-update juga.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>💡 Best Practice:</strong><br>
                        • Buat index pada kolom yang <strong>sering dicari</strong> (WHERE clause)<br>
                        • Buat index pada <strong>Foreign Key</strong> untuk JOIN yang cepat<br>
                        • Jangan buat terlalu banyak index pada tabel yang sering INSERT/UPDATE<br>
                        • Monitor performa dengan EXPLAIN ANALYZE secara berkala
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SQL Code -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-code me-2"></i>SQL Code untuk Membuat Index</h6>
                </div>
                <div class="card-body bg-dark text-light">
                    <code style="color: #fff;">
                        <span style="color: #ff6b6b;">-- Membuat B-Tree Index (Default)</span><br>
                        <span style="color: #4ecdc4;">CREATE INDEX</span> idx_buku_judul <span style="color: #4ecdc4;">ON</span> Buku(judul);<br><br>

                        <span style="color: #ff6b6b;">-- Membuat Partial Index (Index dengan kondisi)</span><br>
                        <span style="color: #4ecdc4;">CREATE INDEX</span> idx_buku_stok_habis <span style="color: #4ecdc4;">ON</span> Buku(judul) <span style="color: #4ecdc4;">WHERE</span> jumlah_stok = 0;<br><br>

                        <span style="color: #ff6b6b;">-- Cek apakah index digunakan</span><br>
                        <span style="color: #4ecdc4;">EXPLAIN ANALYZE</span> <span style="color: #4ecdc4;">SELECT</span> * <span style="color: #4ecdc4;">FROM</span> Buku <span style="color: #4ecdc4;">WHERE</span> judul = 'Laskar Pelangi';<br><br>

                        <span style="color: #ff6b6b;">-- Drop index</span><br>
                        <span style="color: #4ecdc4;">DROP INDEX</span> <span style="color: #4ecdc4;">IF EXISTS</span> idx_buku_judul;
                    </code>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>