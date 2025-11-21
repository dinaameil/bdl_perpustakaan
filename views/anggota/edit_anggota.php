<?php
require_once '../../config/database.php';

// 1. Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: list_anggota.php");
    exit();
}

$id = $_GET['id'];

// 2. Ambil Data Lama
$stmt = $pdo->prepare("SELECT * FROM Anggota WHERE id_anggota = :id");
$stmt->execute([':id' => $id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='list_anggota.php';</script>";
    exit();
}

// 3. Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE Anggota SET 
                nomor_anggota = :no,
                nama_lengkap = :nama,
                jenis_kelamin = :jk,
                alamat = :alamat,
                telepon = :telp,
                email = :email
                WHERE id_anggota = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':no'    => $_POST['nomor_anggota'],
            ':nama'  => $_POST['nama_lengkap'],
            ':jk'    => $_POST['jenis_kelamin'],
            ':alamat'=> $_POST['alamat'],
            ':telp'  => $_POST['telepon'],
            ':email' => $_POST['email'],
            ':id'    => $id
        ]);

        echo "<script>alert('Data berhasil diupdate!'); window.location='list_anggota.php';</script>";
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
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Anggota</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nomor Anggota</label>
                            <input type="text" name="nomor_anggota" class="form-control" value="<?= htmlspecialchars($anggota['nomor_anggota']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($anggota['nama_lengkap']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($anggota['email']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">No. Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($anggota['telepon']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="Laki-laki" <?= ($anggota['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($anggota['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($anggota['alamat']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="list_anggota.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>