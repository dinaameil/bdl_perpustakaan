<?php
require_once '../../config/database.php';

// --- BAGIAN 1: PERSIAPAN DATA ---

// Ambil data untuk Dropdown Anggota
$anggota = $pdo->query("SELECT * FROM Anggota ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ambil data Buku (Hanya yang stoknya > 0)
$buku = $pdo->query("SELECT * FROM Buku WHERE jumlah_stok > 0 ORDER BY judul ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- BAGIAN 2: PROSES TRANSAKSI (POINT 6 TUGAS AKHIR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // 1. MULAI TRANSAKSI (BEGIN)
        // Database menahan perubahan sementara, belum disimpan permanen
        $pdo->beginTransaction();

        // Ambil Input
        $id_anggota = $_POST['id_anggota'];
        $id_buku = $_POST['id_buku'];
        $tgl_pinjam = $_POST['tanggal_pinjam'];
        $jatuh_tempo = $_POST['jatuh_tempo'];

        // 2. CEK STOK (Validasi Business Rules)
        // Kita cek lagi stoknya untuk memastikan tidak ada race condition
        $stmtCek = $pdo->prepare("SELECT jumlah_stok FROM Buku WHERE id_buku = ?");
        $stmtCek->execute([$id_buku]);
        $stokSekarang = $stmtCek->fetchColumn();

        if ($stokSekarang < 1) {
            // Jika stok habis, lempar Error agar masuk ke block catch (Rollback)
            throw new Exception("Stok buku habis! Transaksi dibatalkan.");
        }

        // 3. INSERT KE TABEL PEMINJAMAN
        $sqlPinjam = "INSERT INTO Peminjaman (id_anggota, id_buku, tanggal_pinjam, jatuh_tempo) 
                      VALUES (:anggota, :buku, :tgl, :tempo)";
        $stmtInsert = $pdo->prepare($sqlPinjam);
        $stmtInsert->execute([
            ':anggota' => $id_anggota,
            ':buku' => $id_buku,
            ':tgl' => $tgl_pinjam,
            ':tempo' => $jatuh_tempo
        ]);

        // 4. UPDATE STOK BUKU (Kurangi 1)
        // Ini adalah implementasi "Transaksi Complex" (Insert + Update)
        $sqlUpdate = "UPDATE Buku SET jumlah_stok = jumlah_stok - 1 WHERE id_buku = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':id' => $id_buku]);

        // 5. SIMPAN PERMANEN (COMMIT)
        // Jika langkah 2, 3, dan 4 sukses semua, baru simpan ke database
        $pdo->commit();

        echo "<script>
            alert('Peminjaman Berhasil! Stok buku otomatis berkurang.');
            window.location='peminjaman.php';
        </script>";

    } catch (Exception $e) {
        // 6. BATALKAN SEMUA (ROLLBACK)
        // Jika ada error di langkah manapun (stok habis atau error query),
        // kembalikan database ke kondisi semula.
        $pdo->rollBack();
        $error = "Transaksi Gagal: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Form Peminjaman Buku</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Anggota</label>
                        <select name="id_anggota" class="form-select" required>
                            <option value="">-- Pilih Peminjam --</option>
                            <?php foreach($anggota as $a): ?>
                                <option value="<?= $a['id_anggota'] ?>">
                                    <?= $a['nomor_anggota'] ?> - <?= $a['nama_lengkap'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Buku</label>
                        <select name="id_buku" class="form-select" required>
                            <option value="">-- Pilih Buku (Hanya yang tersedia) --</option>
                            <?php foreach($buku as $b): ?>
                                <option value="<?= $b['id_buku'] ?>">
                                    <?= $b['judul'] ?> (Stok: <?= $b['jumlah_stok'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">Buku dengan stok 0 tidak akan muncul disini.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tanggal Pinjam</label>
                            <input type="date" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow">
                            <i class="fas fa-save me-2"></i>Proses Peminjaman
                        </button>
                        <a href="peminjaman.php" class="btn btn-secondary">Batal</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>