<?php
require_once '../../config/database.php';

// 1. Cek apakah ada ID di URL
if (!isset($_GET['id'])) {
    header("Location: list_buku.php");
    exit();
}

$id_buku = $_GET['id'];

// 2. Ambil data buku yang mau diedit
$stmt = $pdo->prepare("SELECT * FROM Buku WHERE id_buku = :id");
$stmt->execute([':id' => $id_buku]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    die("Buku tidak ditemukan!");
}

// 3. Ambil data dropdown (Penerbit & Kategori)
$penerbit = $pdo->query("SELECT * FROM Penerbit")->fetchAll(PDO::FETCH_ASSOC);
$kategori = $pdo->query("SELECT * FROM Kategori_Buku")->fetchAll(PDO::FETCH_ASSOC);

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

        header("Location: list_buku.php");
        exit();
    } catch (PDOException $e) {
        echo "Gagal update data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Edit Data Buku</h2>
    <form method="POST" action="">
        <label>Judul Buku:</label><br>
        <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required><br><br>

        <label>Pengarang:</label><br>
        <input type="text" name="pengarang" value="<?= htmlspecialchars($buku['pengarang']) ?>" required><br><br>

        <label>ISBN:</label><br>
        <input type="text" name="isbn" value="<?= htmlspecialchars($buku['isbn']) ?>"><br><br>

        <label>Tahun Terbit:</label><br>
        <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit']) ?>"><br><br>

        <label>Stok:</label><br>
        <input type="number" name="jumlah_stok" value="<?= htmlspecialchars($buku['jumlah_stok']) ?>" required><br><br>

        <label>Penerbit:</label><br>
        <select name="id_penerbit" required>
            <?php foreach($penerbit as $p): ?>
                <option value="<?= $p['id_penerbit'] ?>" <?= ($p['id_penerbit'] == $buku['id_penerbit']) ? 'selected' : '' ?>>
                    <?= $p['nama_penerbit'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Kategori:</label><br>
        <select name="id_kategori" required>
            <?php foreach($kategori as $k): ?>
                <option value="<?= $k['id_kategori'] ?>" <?= ($k['id_kategori'] == $buku['id_kategori']) ? 'selected' : '' ?>>
                    <?= $k['nama_kategori'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Update Buku</button>
        <a href="list_buku.php">Batal</a>
    </form>
</body>
</html>