<?php
require_once '../../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: peminjaman.php");
    exit();
}

$id_peminjaman = $_GET['id'];

// 1. Ambil Data Peminjaman
$stmt = $pdo->prepare("SELECT p.*, b.judul, b.id_buku, a.nama_lengkap, a.nomor_anggota 
                       FROM Peminjaman p
                       JOIN Buku b ON p.id_buku = b.id_buku
                       JOIN Anggota a ON p.id_anggota = a.id_anggota
                       WHERE p.id_peminjaman = :id");
$stmt->execute([':id' => $id_peminjaman]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan!");
}

// 2. Hitung Denda Otomatis
$tgl_hari_ini = date('Y-m-d'); // Tanggal kembali = Hari ini
$jatuh_tempo = $data['jatuh_tempo'];
$denda = 0;
$telat_hari = 0;

// Jika Hari Ini > Jatuh Tempo, hitung selisih hari
if ($tgl_hari_ini > $jatuh_tempo) {
    $start = new DateTime($jatuh_tempo);
    $end = new DateTime($tgl_hari_ini);
    $diff = $start->diff($end);
    $telat_hari = $diff->days;
    
    // Tarif Denda: Rp 1.000 per hari (Bisa diubah)
    $denda = $telat_hari * 1000;
}

// 3. PROSES PENGEMBALIAN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // A. Insert ke Tabel Pengembalian
        $sqlInsert = "INSERT INTO Pengembalian (id_peminjaman, tanggal_kembali, denda_keterlambatan, catatan)
                      VALUES (:id_pinjam, :tgl_kembali, :denda, :catatan)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([
            ':id_pinjam' => $id_peminjaman,
            ':tgl_kembali' => $tgl_hari_ini,
            ':denda' => $_POST['denda_final'], // Ambil dari input (kali aja didiskon admin)
            ':catatan' => $_POST['catatan']
        ]);

        // B. Update Stok Buku (NAMBAH +1)
        $sqlUpdate = "UPDATE Buku SET jumlah_stok = jumlah_stok + 1 WHERE id_buku = :id_buku";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':id_buku' => $data['id_buku']]);

        $pdo->commit();

        echo "<script>alert('Buku berhasil dikembalikan! Stok bertambah.'); window.location='pengembalian.php';</script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal memproses: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Konfirmasi Pengembalian</h5>
            </div>
            <div class="card-body p-4">
                
                <div class="alert alert-light border">
                    <strong>Peminjam:</strong> <?= $data['nama_lengkap'] ?> (<?= $data['nomor_anggota'] ?>)<br>
                    <strong>Buku:</strong> <?= $data['judul'] ?><br>
                    <strong>Jatuh Tempo:</strong> <?= date('d M Y', strtotime($data['jatuh_tempo'])) ?>
                </div>

                <form method="POST" action="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Kembali</label>
                        <input type="text" class="form-control" value="<?= date('d F Y') ?>" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Keterlambatan</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-danger fw-bold" value="<?= $telat_hari ?>" readonly>
                                <span class="input-group-text">Hari</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Total Denda</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="denda_final" class="form-control text-danger fw-bold" value="<?= $denda ?>">
                            </div>
                            <small class="text-muted">Tarif: Rp 1.000/hari</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan / Kondisi Buku</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Buku aman, atau cover sobek sedikit..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Proses Kembali
                        </button>
                        <a href="peminjaman.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>