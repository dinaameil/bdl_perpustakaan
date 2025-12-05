<?php
require_once '../../config/database.php';
include '../layouts/header.php';

try {
    // Jika id_pengembalian NULL, berarti BELUM KEMBALI
    $sql = "SELECT p.*, a.nama_lengkap, b.judul, pg.tanggal_kembali
            FROM Peminjaman p
            JOIN Anggota a ON p.id_anggota = a.id_anggota
            JOIN Buku b ON p.id_buku = b.id_buku
            LEFT JOIN Pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
            ORDER BY p.tanggal_pinjam DESC";
            
    $stmt = $pdo->query($sql);
    $peminjaman = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">📚 Sirkulasi Peminjaman</h3>
        <a href="tambah_peminjaman.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i>Transaksi Baru
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($peminjaman as $row): ?>
                        <tr>
                            <td>#<?= $row['id_peminjaman'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                            <td class="text-center">
                                <?php if($row['tanggal_kembali']): ?>
                                    <span class="badge bg-success">Sudah Kembali</span>
                                    <small class="d-block text-muted"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></small>
                                <?php else: ?>
                                    <?php if(date('Y-m-d') > $row['jatuh_tempo']): ?>
                                        <span class="badge bg-danger">Terlambat!</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if(!$row['tanggal_kembali']): ?>
                                    <a href="proses_kembali.php?id=<?= $row['id_peminjaman'] ?>" class="btn btn-sm btn-success" title="Proses Pengembalian">
                                        <i class="fas fa-check-circle"></i> Kembali
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check"></i> Selesai</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>