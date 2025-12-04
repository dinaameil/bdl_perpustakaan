<?php
require_once '../../config/database.php';

session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit;
}

// === PAGINATION & SEARCH SETUP ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10; // Records per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // === HITUNG TOTAL DATA (untuk pagination) ===
    $sql_count = "SELECT COUNT(*) FROM Buku b
                  JOIN Penerbit p ON b.id_penerbit = p.id_penerbit
                  JOIN Kategori_Buku k ON b.id_kategori = k.id_kategori
                  WHERE b.judul ILIKE :search OR b.pengarang ILIKE :search";
    
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':search' => "%$search%"]);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // === AMBIL DATA DENGAN PAGINATION ===
    $sql = "SELECT b.*, p.nama_penerbit, k.nama_kategori 
            FROM Buku b
            JOIN Penerbit p ON b.id_penerbit = p.id_penerbit
            JOIN Kategori_Buku k ON b.id_kategori = k.id_kategori
            WHERE b.judul ILIKE :search OR b.pengarang ILIKE :search
            ORDER BY b.id_buku ASC
            LIMIT :limit OFFSET :offset";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $daftar_buku = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">📚 Daftar Koleksi Buku</h3>
            <small class="text-muted">Total: <?= $total_records ?> buku | Halaman <?= $page ?> dari <?= $total_pages ?></small>
        </div>
        <a href="tambah_buku.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>Tambah Buku
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            
            <!-- REAL-TIME SEARCH BOX -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="searchInput" 
                               class="form-control" 
                               placeholder="🔍 Cari berdasarkan judul atau pengarang..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <small class="text-muted">Real-time search: ketik langsung, hasil otomatis muncul</small>
                </div>
                <div class="col-md-6 text-end">
                    <span id="searchStatus" class="badge bg-secondary">Siap mencari...</span>
                </div>
            </div>

            <!-- LOADING INDICATOR -->
            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Mencari data...</p>
            </div>

            <!-- DATA TABLE -->
            <div class="table-responsive" id="tableContainer">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($daftar_buku) > 0): ?>
                            <?php foreach($daftar_buku as $buku): ?>
                            <tr>
                                <td>#<?= $buku['id_buku'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></td>
                                <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($buku['nama_kategori']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($buku['nama_penerbit']) ?></td>
                                <td class="text-center">
                                    <?php if($buku['jumlah_stok'] < 5): ?>
                                        <span class="badge bg-danger"><?= $buku['jumlah_stok'] ?> (Habis)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= $buku['jumlah_stok'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="edit_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-sm btn-warning text-white me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapus_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                    Tidak ada buku ditemukan untuk "<?= htmlspecialchars($search) ?>"
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    
                    <!-- Previous Button -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">1</a>
                        </li>
                        <?php if($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if($end_page < $total_pages): ?>
                        <?php if($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- JAVASCRIPT: REAL-TIME SEARCH -->
<script>
// Debounce function untuk menghindari terlalu banyak request
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Real-time search handler
const searchInput = document.getElementById('searchInput');
const searchStatus = document.getElementById('searchStatus');
const loadingIndicator = document.getElementById('loadingIndicator');

searchInput.addEventListener('input', debounce(function(e) {
    const searchTerm = e.target.value.trim();
    
    // Update status
    searchStatus.className = 'badge bg-warning';
    searchStatus.textContent = 'Mencari...';
    
    // Show loading
    loadingIndicator.style.display = 'block';
    
    // Redirect dengan parameter search (auto refresh halaman)
    const currentPage = <?= $page ?>;
    window.location.href = `?page=1&search=${encodeURIComponent(searchTerm)}`;
    
}, 800)); // Tunggu 800ms setelah user berhenti mengetik

// Update status saat halaman load
window.addEventListener('load', function() {
    const searchValue = searchInput.value.trim();
    if(searchValue) {
        searchStatus.className = 'badge bg-success';
        searchStatus.textContent = `Hasil untuk: "${searchValue}"`;
    }
});
</script>

<style>
/* Custom styling untuk pagination active */
.pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Hover effect untuk search input */
#searchInput:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

/* Smooth transition untuk loading */
#loadingIndicator {
    transition: all 0.3s ease;
}
</style>

<?php include '../layouts/footer.php'; ?>