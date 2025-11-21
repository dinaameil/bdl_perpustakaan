<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM Anggota WHERE id_anggota = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Kalau sukses, redirect
        header("Location: list_anggota.php");
    } catch (PDOException $e) {
        // Kalau GAGAL (biasanya karena anggota ini datanya dipakai di tabel Peminjaman)
        // Kita tangkap errornya biar web gak crash, lalu kasih pesan alert
        echo "<script>
                alert('GAGAL MENGHAPUS! Anggota ini memiliki riwayat peminjaman buku. Hapus dulu data peminjamannya jika ingin menghapus anggota ini.');
                window.location='list_anggota.php';
              </script>";
    }
} else {
    header("Location: list_anggota.php");
}
?>