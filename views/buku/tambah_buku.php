<?php
require_once '../../config/database.php';

// --- BAGIAN 1: LOGIC PHP ---

// 1. Ambil data Penerbit & Kategori dari Database (INI YANG TADI HILANG)
try {
    $penerbit = $pdo->query("SELECT * FROM Penerbit ORDER BY nama_penerbit ASC")->fetchAll(PDO::FETCH_ASSOC);
    $kategori = $pdo->query("SELECT * FROM Kategori_Buku ORDER BY nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data referensi: " . $e->getMessage());
}

// 2. Proses Simpan saat tombol ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO Buku (judul, pengarang, isbn, tahun_terbit, jumlah_stok, id_penerbit, id_kategori) 
                VALUES (:judul, :pengarang, :isbn, :tahun, :stok, :penerbit, :kategori)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':judul' => $_POST['judul'],
            ':pengarang' => $_POST['pengarang'],
            ':isbn' => $_POST['isbn'],
            ':tahun' => $_POST['tahun_terbit'],
            ':stok' => $_POST['jumlah_stok'],
            ':penerbit' => $_POST['id_penerbit'],
            ':kategori' => $_POST['id_kategori']
        ]);

        // Redirect balik ke list buku setelah sukses
        echo "<script>alert('Buku berhasil ditambahkan!'); window.location='list_buku.php';</script>";
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambah data: " . $e->getMessage();
    }
}

// --- BAGIAN 2: TAMPILAN HTML ---
include '../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Tambah Buku Baru</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Buku</label>
                        <input type="text" name="judul" class="form-control" placeholder="Masukkan judul buku..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok Awal</label>
                            <input type="number" name="jumlah_stok" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Penerbit</label>
                        <select name="id_penerbit" class="form-select" required>
                            <option value="">-- Pilih Penerbit --</option>
                            <?php foreach($penerbit as $p): ?>
                                <option value="<?= $p['id_penerbit'] ?>"><?= $p['nama_penerbit'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($kategori as $k): ?>
                                <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan Data</button>
                        <a href="list_buku.php" class="btn btn-secondary">Batal</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>