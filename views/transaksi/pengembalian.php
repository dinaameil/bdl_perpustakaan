<?php
require_once '../../config/database.php';
include '../layouts/header.php';

// Query: Ambil data dari tabel Pengembalian JOIN ke Peminjaman, Anggota, Buku
$sql = "SELECT pg.*, p.tanggal_pinjam, a.nama_lengkap, b.judul 
        FROM Pengembalian pg
        JOIN Peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN Anggota a ON p.id_anggota = a.id_anggota
        JOIN Buku b ON p.id_buku = b.id_buku
        ORDER BY pg.tanggal_kembali DESC";

$stmt = $pdo->query($sql);
$histori = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">📂 Riwayat Pengembalian</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Balik</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($histori as $row): ?>
                        <tr>
                            <td>#<?= $row['id_pengembalian'] ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= date('d/m/y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td class="fw-bold text-success"><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></td>
                            <td>
                                <?php if($row['denda_keterlambatan'] > 0): ?>
                                    <span class="badge bg-danger">Rp <?= number_format($row['denda_keterlambatan'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($row['catatan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>