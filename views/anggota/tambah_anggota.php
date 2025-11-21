<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Kita pakai TRANSACTION biar keren dan aman
        $pdo->beginTransaction();

        // OPSI 1: Pakai Stored Procedure yang kemarin dibuat (Nilai Plus +++) 
        // Query: CALL tambah_anggota_baru('A-001', 'Nama', '08123', 'email@mail.com')
        $sql = "CALL tambah_anggota_baru(:no, :nama, :telp, :email)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':no'    => $_POST['nomor_anggota'],
            ':nama'  => $_POST['nama_lengkap'],
            ':telp'  => $_POST['telepon'],
            ':email' => $_POST['email']
        ]);

        /* CATATAN: Kalau Stored Procedure belum dibuat/error, pakai cara biasa (Insert Into) di bawah ini:
           $sql = "INSERT INTO Anggota (nomor_anggota, nama_lengkap, jenis_kelamin, alamat, telepon, email) VALUES (...)";
        */

        // Update sisa data yang tidak ada di procedure (opsional, misal alamat/jk)
        // Karena procedure kemarin cuma 4 parameter, kita update sisanya manual
        // Ambil ID anggota barusan (untuk update alamat & JK)
        // Tapi untuk simpelnya, anggap saja procedure sudah handle atau kita pakai INSERT biasa jika mau lengkap.
        
        // Agar tidak bingung, kita pakai INSERT BIASA saja dulu biar semua kolom terisi lengkap,
        // Nanti Procedure kita panggil di menu khusus "Test Procedure" biar aman.
        
        // -- Revisi Strategi --
        // Kita pakai INSERT biasa dulu untuk fitur utama biar data Alamat & JK masuk.
        $pdo->rollBack(); // Batalkan call procedure tadi buat demo
        
        // Insert Normal
        $sql_insert = "INSERT INTO Anggota (nomor_anggota, nama_lengkap, jenis_kelamin, alamat, telepon, email) 
                       VALUES (:no, :nama, :jk, :alamat, :telp, :email)";
        $stmt = $pdo->prepare($sql_insert);
        $stmt->execute([
            ':no'     => $_POST['nomor_anggota'],
            ':nama'   => $_POST['nama_lengkap'],
            ':jk'     => $_POST['jenis_kelamin'],
            ':alamat' => $_POST['alamat'],
            ':telp'   => $_POST['telepon'],
            ':email'  => $_POST['email']
        ]);

        // Commit simpan data
        // $pdo->commit(); // Jika pakai transaction manual

        echo "<script>alert('Berhasil tambah anggota!'); window.location='list_anggota.php';</script>";
        
    } catch (PDOException $e) {
        // $pdo->rollBack();
        $error = "Gagal menambah data: " . $e->getMessage();
    }
}

include '../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrasi Anggota Baru</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nomor Anggota</label>
                            <input type="text" name="nomor_anggota" class="form-control" placeholder="Misal: A-100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">No. Telepon</label>
                            <input type="text" name="telepon" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan Anggota</button>
                        <a href="list_anggota.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>