<?php
require_once '../../config/database.php';

// Panggil Header
include '../layouts/header.php';

// Query ambil semua data anggota
try {
    $sql = "SELECT * FROM Anggota ORDER BY id_anggota DESC";
    $stmt = $pdo->query($sql);
    $anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">👥 Data Anggota Perpustakaan</h3>
        <a href="tambah_anggota.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-user-plus me-2"></i>Daftar Anggota Baru
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($anggota as $a): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($a['nomor_anggota']) ?></td>
                            <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($a['jenis_kelamin']) ?></td>
                            <td>
                                <small class="d-block"><i class="fas fa-phone me-1 text-muted"></i> <?= htmlspecialchars($a['telepon']) ?></small>
                                <small class="d-block"><i class="fas fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($a['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($a['alamat']) ?></td>
                            <td class="text-center">
                                <a href="edit_anggota.php?id=<?= $a['id_anggota'] ?>" class="btn btn-sm btn-warning text-white me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="hapus_anggota.php?id=<?= $a['id_anggota'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus anggota ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
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