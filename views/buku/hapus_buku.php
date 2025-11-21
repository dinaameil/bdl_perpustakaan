<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        // Query Delete
        $sql = "DELETE FROM Buku WHERE id_buku = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Balik ke halaman list
        header("Location: list_buku.php");
    } catch (PDOException $e) {
        // Error handling kalau buku sedang dipinjam (Constraint Violation)
        echo "Gagal menghapus buku! Buku ini mungkin sedang dipinjam atau ada di riwayat transaksi.<br>";
        echo "Error detail: " . $e->getMessage();
        echo "<br><a href='list_buku.php'>Kembali</a>";
    }
} else {
    header("Location: list_buku.php");
}
?>