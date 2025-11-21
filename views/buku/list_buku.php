<?php
// --- BAGIAN 1: LOGIC PHP (JANGAN DIHAPUS) ---
require_once '../../config/database.php';

// Cek sesi login (Opsional, biar aman)
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit;
}

try {
    // Query untuk mengambil data buku
    $sql = "SELECT b.*, p.nama_penerbit, k.nama_kategori 
            FROM Buku b
            JOIN Penerbit p ON b.id_penerbit = p.id_penerbit
            JOIN Kategori_Buku k ON b.id_kategori = k.id_kategori
            ORDER BY b.id_buku ASC";
            
    $stmt = $pdo->query($sql);
    // Hasil query disimpan ke variabel $daftar_buku
    $daftar_buku = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// --- BAGIAN 2: TAMPILAN HTML (HEADER & TABLE) ---
include '../layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">📚 Daftar Koleksi Buku</h3>
        <a href="tambah_buku.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>Tambah Buku
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($daftar_buku as $buku): ?>
                        <tr>
                            <td>#<?= $buku['id_buku'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></td>
                            <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= htmlspecialchars($buku['nama_kategori']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($buku['nama_penerbit']) ?></td>
                            <td class="text-center">
                                <?php if($buku['jumlah_stok'] < 5): ?>
                                    <span class="badge bg-danger"><?= $buku['jumlah_stok'] ?> (Habis)</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $buku['jumlah_stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="edit_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-sm btn-warning text-white me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="hapus_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')" title="Hapus">
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

<?php 
include '../layouts/footer.php'; 
?>