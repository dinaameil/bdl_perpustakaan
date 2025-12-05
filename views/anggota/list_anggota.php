<?php
require_once '../../config/database.php';

session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit;
}

// === SOFT DELETE: Tambahkan kolom deleted_at jika belum ada ===
try {
    // Cek apakah kolom deleted_at sudah ada
    $check = $pdo->query("SELECT column_name FROM information_schema.columns 
                          WHERE table_name='anggota' AND column_name='deleted_at'")->fetchColumn();
    
    if(!$check) {
        // Tambahkan kolom deleted_at
        $pdo->exec("ALTER TABLE Anggota ADD COLUMN deleted_at TIMESTAMP NULL");
    }
} catch(PDOException $e) {
    // Ignore error jika kolom sudah ada
}

// === PAGINATION & SEARCH SETUP ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$show_deleted = isset($_GET['show_deleted']) ? true : false; // Toggle untuk show/hide data terhapus
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // === WHERE CLAUSE: Filter deleted data ===
    $where_deleted = $show_deleted ? "" : "AND deleted_at IS NULL";
    
    // === HITUNG TOTAL DATA ===
    $sql_count = "SELECT COUNT(*) FROM Anggota 
                  WHERE (nama_lengkap ILIKE :search OR nomor_anggota ILIKE :search OR email ILIKE :search)
                  $where_deleted";
    
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':search' => "%$search%"]);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // === AMBIL DATA ===
    $sql = "SELECT * FROM Anggota 
            WHERE (nama_lengkap ILIKE :search OR nomor_anggota ILIKE :search OR email ILIKE :search)
            $where_deleted
            ORDER BY id_anggota DESC
            LIMIT :limit OFFSET :offset";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../layouts/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">👥 Data Anggota Perpustakaan</h3>
        </div>
        <a href="tambah_anggota.php" class="btn btn-primary shadow-sm"
           style="background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; transition: all 0.3s ease;"
           onmouseover="this.style.backgroundColor='#d6669a'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(231, 119, 167, 0.4)';"
           onmouseout="this.style.backgroundColor='var(--primary-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)';">
            <i class="fas fa-user-plus me-2"></i>Daftar Anggota Baru
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            
            <!-- SEARCH BOX & FILTER -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="searchInput" 
                               class="form-control" 
                               placeholder="🔍 Cari nama, nomor anggota, atau email..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <!-- Toggle Show/Hide Deleted -->
                    
                </div>
            </div>

            <!-- LOADING INDICATOR -->
            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Memuat data...</p>
            </div>

            <!-- DATA TABLE -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($anggota) > 0): ?>
                            <?php foreach($anggota as $a): ?>
                            <tr class="<?= $a['deleted_at'] ? 'table-danger' : '' ?>">
                                <td class="fw-bold text-primary"><?= htmlspecialchars($a['nomor_anggota']) ?></td>
                                <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($a['jenis_kelamin']) ?></td>
                                <td>
                                    <small class="d-block"><i class="fas fa-phone me-1 text-muted"></i> <?= htmlspecialchars($a['telepon']) ?></small>
                                    <small class="d-block"><i class="fas fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($a['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($a['alamat']) ?></td>
                                <td class="text-center">
                                    <?php if($a['deleted_at']): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-trash me-1"></i>Dihapus
                                        </span>
                                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['deleted_at'])) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(!$a['deleted_at']): ?>
                                        <!-- Data Aktif: Bisa Edit & Soft Delete -->
                                        <a href="edit_anggota.php?id=<?= $a['id_anggota'] ?>" class="btn btn-sm btn-warning text-white me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus_anggota.php?id=<?= $a['id_anggota'] ?>&soft=1" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus anggota ini? (Soft Delete)')" title="Soft Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <!-- Data Terhapus: Bisa Restore & Permanent Delete -->
                                        <a href="restore_anggota.php?id=<?= $a['id_anggota'] ?>" class="btn btn-sm btn-success me-1" title="Restore">
                                            <i class="fas fa-undo"></i> Restore
                                        </a>
                                        <a href="hapus_anggota.php?id=<?= $a['id_anggota'] ?>&permanent=1" class="btn btn-sm btn-dark" onclick="return confirm('PERHATIAN! Ini akan menghapus PERMANEN dari database. Yakin?')" title="Delete Permanent">
                                            <i class="fas fa-times"></i> Hapus Permanen
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                    Tidak ada data ditemukan
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
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?><?= $show_deleted ? '&show_deleted=1' : '' ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>

                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?><?= $show_deleted ? '&show_deleted=1' : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?><?= $show_deleted ? '&show_deleted=1' : '' ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
// Debounce untuk real-time search
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Real-time search
const searchInput = document.getElementById('searchInput');
const loadingIndicator = document.getElementById('loadingIndicator');

searchInput.addEventListener('input', debounce(function(e) {
    const searchTerm = e.target.value.trim();
    loadingIndicator.style.display = 'block';
    
    // Cek apakah ada checkbox showDeleted, jika tidak ada maka kosongkan
    const showDeletedCheckbox = document.getElementById('showDeleted');
    const showDeleted = (showDeletedCheckbox && showDeletedCheckbox.checked) ? '&show_deleted=1' : '';
    
    window.location.href = `?page=1&search=${encodeURIComponent(searchTerm)}${showDeleted}`;
}, 800));

// Toggle show deleted (jika ada checkbox-nya)
function toggleDeleted(checkbox) {
    const searchTerm = searchInput.value.trim();
    const showDeleted = checkbox.checked ? '&show_deleted=1' : '';
    window.location.href = `?page=1&search=${encodeURIComponent(searchTerm)}${showDeleted}`;
}
</script>

<?php include '../layouts/footer.php'; ?>