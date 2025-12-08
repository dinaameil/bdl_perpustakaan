<?php
require_once '../../config/database.php';
include '../layouts/header.php';

// === SIMPLE VIEW ===
$simple_view = $pdo->query("SELECT * FROM view_stok_buku_hampir_habis")->fetchAll(PDO::FETCH_ASSOC);

// === COMPLEX VIEW dengan Filter & Sorting ===
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'tanggal_pinjam';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Query Complex View
$sql = "SELECT * FROM view_laporan_peminjaman_lengkap WHERE 1=1";

if($filter_kategori) {
    $sql .= " AND \"Kategori\" = :kategori";
}

$sql .= " ORDER BY " . $sort_by . " " . $sort_order;

$stmt = $pdo->prepare($sql);
if($filter_kategori) {
    $stmt->execute([':kategori' => $filter_kategori]);
} else {
    $stmt->execute();
}
$complex_view = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil list kategori untuk filter
$kategori_list = $pdo->query("SELECT DISTINCT nama_kategori FROM Kategori_Buku ORDER BY nama_kategori")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">📊 Demonstrasi Database Views</h3>

    <!-- SIMPLE VIEW -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Stok Buku Hampir Habis</h5>
        </div>
        <div class="card-body">
            
            <?php if(count($simple_view) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Buku</th>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th class="text-center">Stok Tersisa</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($simple_view as $row): ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['id_buku'] ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['pengarang']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= ($row['jumlah_stok'] == 0) ? 'bg-danger' : 'bg-warning text-dark' ?> fs-6">
                                        <?= $row['jumlah_stok'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if($row['jumlah_stok'] == 0): ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Hampir Habis</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>Semua buku memiliki stok yang cukup!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- COMPLEX VIEW -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Laporan Peminjaman Lengkap</h5>
            <span class="badge bg-light text-dark"><?= count($complex_view) ?> Records</span>
        </div>
        <div class="card-body">

            <!-- Filter & Sorting -->
            <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filter Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value=""> Semua Kategori</option>
                        <?php foreach($kategori_list as $kat): ?>
                            <option value="<?= $kat ?>" <?= ($filter_kategori == $kat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sort By</label>
                   <select name="sort" class="form-select">
                        <option value="tanggal_pinjam" <?= ($sort_by == 'tanggal_pinjam') ? 'selected' : '' ?>>Tanggal Pinjam</option>
                        
                        <option value='"Nama Anggota"' <?= ($sort_by == '"Nama Anggota"') ? 'selected' : '' ?>>Nama Anggota</option>
                        <option value='"Denda"' <?= ($sort_by == '"Denda"') ? 'selected' : '' ?>>Denda</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Order</label>
                    <select name="order" class="form-select">
                        <option value="DESC" <?= ($sort_order == 'DESC') ? 'selected' : '' ?>>Descending (Z-A)</option>
                        <option value="ASC" <?= ($sort_order == 'ASC') ? 'selected' : '' ?>>Ascending (A-Z)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" 
                            style="background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#d6669a'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(231, 119, 167, 0.4)';"
                            onmouseout="this.style.backgroundColor='var(--primary-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>

            <!-- Tabel Data -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Anggota</th>
                            <th>Judul Buku</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tgl Kembali</th>
                            <th class="text-end">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($complex_view) > 0): ?>
                            <?php foreach($complex_view as $row): ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['id_peminjaman'] ?></td>
                                <td><?= htmlspecialchars($row['Nama Anggota']) ?></td>
                                <td><?= htmlspecialchars($row['Judul Buku']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['Kategori']) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['Penerbit']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['jatuh_tempo'])) ?></td>
                                <td>
                                    <?php if($row['tanggal_kembali']): ?>
                                        <span class="text-success fw-bold"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Belum Kembali</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($row['Denda'] > 0): ?>
                                        <span class="text-danger fw-bold">Rp <?= number_format($row['Denda'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada data dengan filter tersebut</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<?php include '../layouts/footer.php'; ?>