<?php
// --- BAGIAN 1: LOGIC PHP ---
require_once '../../config/database.php';

// Cek sesi login
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../../login.php");
    exit;
}

// Proses Simpan saat tombol ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Query Insert Data Anggota
        $sql = "INSERT INTO Anggota (nomor_anggota, nama_lengkap, jenis_kelamin, alamat, telepon, email) 
                VALUES (:no, :nama, :jk, :alamat, :telp, :email)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':no'     => $_POST['nomor_anggota'],
            ':nama'   => $_POST['nama_lengkap'],
            ':jk'     => $_POST['jenis_kelamin'],
            ':alamat' => $_POST['alamat'],
            ':telp'   => $_POST['telepon'],
            ':email'  => $_POST['email']
        ]);

        // Redirect balik ke list anggota setelah sukses
        echo "<script>alert('Anggota berhasil didaftarkan!'); window.location='list_anggota.php';</script>";
        exit();
        
    } catch (PDOException $e) {
        // Jika error (misal nomor anggota kembar), tampilkan pesan
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
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrasi Anggota Baru</h5>
            </div>
            
            <div class="card-body p-4">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nomor Anggota</label>
                            <input type="text" name="nomor_anggota" class="form-control" placeholder="Misal: A-001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama sesuai KTP" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">No. Telepon</label>
                            <input type="text" name="telepon" class="form-control" placeholder="08..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat domisili..." required></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg"
                                style="background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#d6669a'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(231, 119, 167, 0.4)';"
                                onmouseout="this.style.backgroundColor='var(--primary-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <i class="fas fa-save me-2"></i>Simpan Data Anggota
                        </button>
                        <a href="list_anggota.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>