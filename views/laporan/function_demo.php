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

// 3. STORED PROCEDURE: Tambah Anggota Baru
if(isset($_POST['tambah_via_sp'])) {
    try {
        $sql = "CALL tambah_anggota_baru(:no, :nama, :telp, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':no' => $_POST['nomor_anggota'],
            ':nama' => $_POST['nama_lengkap'],
            ':telp' => $_POST['telepon'],
            ':email' => $_POST['email']
        ]);
        $success_sp = "✅ Anggota berhasil ditambahkan via Stored Procedure!";
    } catch(PDOException $e) {
        $error = "Error Stored Procedure: " . $e->getMessage();
    }
}

// Ambil data untuk dropdown
$anggota_list = $pdo->query("SELECT id_anggota, nama_lengkap FROM Anggota ORDER BY nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">🔧 Demonstrasi Function & Stored Procedure</h3>

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
                    <h6 class="mb-0"><i class="fas fa-function me-2"></i>Function 1: Hitung Total Denda Anggota</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Function scalar yang menghitung total denda keterlambatan dari seorang anggota.</p>
                    
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
                        <button type="submit" name="cek_denda" class="btn btn-primary w-100">
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
                    <h6 class="mb-0"><i class="fas fa-search me-2"></i>Function 2: Cari Buku by Pengarang</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Function yang mengembalikan tabel hasil pencarian buku berdasarkan nama pengarang.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" placeholder="Contoh: Andrea Hirata" 
                                   value="<?= isset($_POST['pengarang']) ? htmlspecialchars($_POST['pengarang']) : '' ?>" required>
                        </div>
                        <button type="submit" name="cari_buku" class="btn btn-info w-100 text-white">
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

    <!-- STORED PROCEDURE: Tambah Anggota -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-database me-2"></i>Stored Procedure: Tambah Anggota Baru</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Stored procedure untuk menambahkan anggota baru ke database menggunakan CALL statement.</p>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Nomor Anggota</label>
                                <input type="text" name="nomor_anggota" class="form-control" placeholder="A-XXX" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">No. Telepon</label>
                                <input type="text" name="telepon" class="form-control" placeholder="08xxx" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                            </div>
                        </div>
                        <button type="submit" name="tambah_via_sp" class="btn btn-success">
                            <i class="fas fa-plus-circle me-2"></i>Tambah via Stored Procedure
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Penjelasan Teknis -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-info"><i class="fas fa-info-circle me-2"></i>Penjelasan Teknis</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="fw-bold">Function Scalar</h6>
                            <p class="small text-muted">Function yang mengembalikan single value (scalar). Dipanggil dengan SELECT.</p>
                            <code>SELECT hitung_total_denda_anggota(1);</code>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold">Function Table</h6>
                            <p class="small text-muted">Function yang mengembalikan tabel. Bisa digunakan seperti tabel biasa.</p>
                            <code>SELECT * FROM cari_buku_by_pengarang('Andrea');</code>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold">Stored Procedure</h6>
                            <p class="small text-muted">Prosedur yang dapat melakukan INSERT/UPDATE/DELETE. Dipanggil dengan CALL.</p>
                            <code>CALL tambah_anggota_baru('A-99', ...);</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>