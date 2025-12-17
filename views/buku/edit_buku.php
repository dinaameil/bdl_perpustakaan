<?php
require_once '../../config/database.php';

// 1. Cek apakah ada ID di URL
if (!isset($_GET['id'])) {
    header("Location: list_buku.php");
    exit();
}

$id_buku = $_GET['id'];
$message = '';

// 2. Ambil data buku yang mau diedit
$stmt = $pdo->prepare("SELECT * FROM Buku WHERE id_buku = :id");
$stmt->execute([':id' => $id_buku]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    die("Buku tidak ditemukan!");
}

// 3. Ambil data dropdown (Penerbit & Kategori)
$penerbit = $pdo->query("SELECT id_penerbit, nama_penerbit FROM Penerbit ORDER BY nama_penerbit ASC")->fetchAll(PDO::FETCH_ASSOC);
$kategori = $pdo->query("SELECT id_kategori, nama_kategori FROM Kategori_Buku ORDER BY nama_kategori ASC")->fetchAll(PDO::FETCH_ASSOC);

// 4. Proses Update saat tombol Simpan ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE Buku SET 
                judul = :judul, 
                pengarang = :pengarang, 
                isbn = :isbn, 
                tahun_terbit = :tahun, 
                jumlah_stok = :stok, 
                id_penerbit = :penerbit, 
                id_kategori = :kategori 
                WHERE id_buku = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':judul' => $_POST['judul'],
            ':pengarang' => $_POST['pengarang'],
            ':isbn' => $_POST['isbn'],
            ':tahun' => $_POST['tahun_terbit'],
            ':stok' => $_POST['jumlah_stok'],
            ':penerbit' => $_POST['id_penerbit'],
            ':kategori' => $_POST['id_kategori'],
            ':id' => $id_buku
        ]);

        echo "<script>alert('Data buku berhasil diupdate!'); window.location='list_buku.php';</script>";

    } catch (PDOException $e) {
        $error = "Gagal update data: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Data Buku</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Judul Buku</label>
                            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($buku['judul']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Pengarang</label>
                            <input type="text" name="pengarang" class="form-control" value="<?= htmlspecialchars($buku['pengarang']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($buku['isbn']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control" value="<?= htmlspecialchars($buku['tahun_terbit']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Stok</label>
                        <input type="number" name="jumlah_stok" class="form-control" value="<?= htmlspecialchars($buku['jumlah_stok']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Penerbit</label>
                        <select name="id_penerbit" class="form-select" required>
                            <option value="">-- Pilih Penerbit --</option>
                            <?php foreach($penerbit as $p): ?>
                                <option value="<?= $p['id_penerbit'] ?>" <?= ($p['id_penerbit'] == $buku['id_penerbit']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_penerbit']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($kategori as $k): ?>
                                <option value="<?= $k['id_kategori'] ?>" <?= ($k['id_kategori'] == $buku['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow"
                                style="background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#d6669a'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(231, 119, 167, 0.4)';"
                                onmouseout="this.style.backgroundColor='var(--primary-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.2)';">
                            <i class="fas fa-save me-2"></i>Update Buku
                        </button>
                        <a href="list_buku.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>