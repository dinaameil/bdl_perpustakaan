<?php
require_once '../../config/database.php';
include '../layouts/header.php';

$hasil_denda = null;
$hasil_cari_buku = null;
$success_sp = null;
$error = null;

// 1. FUNCTION: Hitung Total Denda Anggota
if(isset($_POST['cek_denda'])) {
    try {
        $id_anggota = $_POST['id_anggota'];
        $stmt = $pdo->prepare("SELECT hitung_total_denda_anggota(:id)");
        $stmt->execute([':id' => $id_anggota]);
        $hasil_denda = $stmt->fetchColumn();
        
        // Ambil nama anggota
        $stmt2 = $pdo->prepare("SELECT nama_lengkap FROM Anggota WHERE id_anggota = :id");
        $stmt2->execute([':id' => $id_anggota]);
        $nama_anggota = $stmt2->fetchColumn();
    } catch(PDOException $e) {
        $error = "Error Function 1: " . $e->getMessage();
    }
}

// 2. FUNCTION: Cari Buku by Pengarang
if(isset($_POST['cari_buku'])) {
    try {
        $pengarang = $_POST['pengarang'];
        $stmt = $pdo->prepare("SELECT * FROM cari_buku_by_pengarang(:nama)");
        $stmt->execute([':nama' => $pengarang]);
        $hasil_cari_buku = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error Function 2: " . $e->getMessage();
    }
}

// Ambil data untuk dropdown
$anggota_list = $pdo->query("SELECT id_anggota, nama_lengkap FROM Anggota ORDER BY nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Hover effect untuk tombol Hitung Total Denda - Pink Theme */
.btn-hitung-denda {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    transition: all 0.3s ease;
}

.btn-hitung-denda:hover {
    background-color: #d6669a !important; /* Pink lebih gelap */
    border-color: #d6669a !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 119, 167, 0.4);
}

/* Hover effect untuk tombol Cari Buku - Info Theme */
.btn-cari-buku {
    transition: all 0.3s ease;
}

.btn-cari-buku:hover {
    background-color: #5bc0de !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
}
</style>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">🔧 Function & Stored Procedure</h3>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($success_sp): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_sp) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- FUNCTION 1: Hitung Total Denda -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-function me-2"></i>Hitung Total Denda Anggota</h6>
                </div>
                <div class="card-body">
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Anggota</label>
                            <select name="id_anggota" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                <?php foreach($anggota_list as $a): ?>
                                    <option value="<?= $a['id_anggota'] ?>" <?= (isset($_POST['id_anggota']) && $_POST['id_anggota'] == $a['id_anggota']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['nama_lengkap']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="cek_denda" class="btn btn-primary btn-hitung-denda w-100">
                            <i class="fas fa-calculator me-2"></i>Hitung Total Denda
                        </button>
                    </form>
                    
                    <?php if($hasil_denda !== null): ?>
                        <div class="alert alert-success mt-3 mb-0">
                            <strong>📊 Hasil Function:</strong><br>
                            <span class="text-muted">Anggota: <?= htmlspecialchars($nama_anggota) ?></span><br>
                            <h4 class="mb-0 mt-2">Rp <?= number_format($hasil_denda, 0, ',', '.') ?></h4>
                            <small class="text-muted">Total denda yang harus dibayar</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- FUNCTION 2: Cari Buku by Pengarang -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-search me-2"></i>Cari Buku by Pengarang</h6>
                </div>
                <div class="card-body">                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" placeholder="Contoh: Andrea Hirata" 
                                   value="<?= isset($_POST['pengarang']) ? htmlspecialchars($_POST['pengarang']) : '' ?>" required>
                        </div>
                        <button type="submit" name="cari_buku" class="btn btn-info btn-cari-buku w-100 text-white">
                            <i class="fas fa-search me-2"></i>Cari Buku
                        </button>
                    </form>
                    
                    <?php if($hasil_cari_buku !== null): ?>
                        <div class="mt-3">
                            <strong>📚 Hasil Pencarian: <?= count($hasil_cari_buku) ?> Buku</strong>
                            <?php if(count($hasil_cari_buku) > 0): ?>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Judul</th>
                                                <th>ISBN</th>
                                                <th>Tahun</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($hasil_cari_buku as $buku): ?>
                                            <tr>
                                                <td><?= $buku['id_buku'] ?></td>
                                                <td><?= htmlspecialchars($buku['judul']) ?></td>
                                                <td><?= htmlspecialchars($buku['isbn']) ?></td>
                                                <td><?= $buku['tahun_terbit'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-2 mb-0">
                                    Tidak ada buku ditemukan untuk pengarang "<?= htmlspecialchars($_POST['pengarang']) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include '../layouts/footer.php'; ?>