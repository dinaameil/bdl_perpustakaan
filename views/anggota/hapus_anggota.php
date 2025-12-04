<?php
require_once '../../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: list_anggota.php");
    exit();
}

$id = $_GET['id'];
$is_soft_delete = isset($_GET['soft']) && $_GET['soft'] == 1;
$is_permanent = isset($_GET['permanent']) && $_GET['permanent'] == 1;

try {
    if ($is_permanent) {
        // === PERMANENT DELETE (Hard Delete) ===
        // Cek apakah anggota ini punya riwayat peminjaman
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Peminjaman WHERE id_anggota = :id");
        $stmt_check->execute([':id' => $id]);
        $has_peminjaman = $stmt_check->fetchColumn();

        if ($has_peminjaman > 0) {
            echo "<script>
                    alert('GAGAL! Anggota ini memiliki riwayat peminjaman. Tidak bisa dihapus permanen untuk menjaga integritas data.');
                    window.location='list_anggota.php?show_deleted=1';
                  </script>";
            exit();
        }

        // Hapus permanen
        $sql = "DELETE FROM Anggota WHERE id_anggota = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        echo "<script>
                alert('✅ Anggota berhasil dihapus PERMANEN dari database!');
                window.location='list_anggota.php?show_deleted=1';
              </script>";

    } elseif ($is_soft_delete) {
        // === SOFT DELETE ===
        // Update kolom deleted_at dengan timestamp sekarang
        $sql = "UPDATE Anggota SET deleted_at = CURRENT_TIMESTAMP WHERE id_anggota = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        echo "<script>
                alert('✅ Anggota berhasil di-Soft Delete! Data masih bisa di-restore.');
                window.location='list_anggota.php';
              </script>";
    } else {
        // Default: redirect ke list
        header("Location: list_anggota.php");
    }

} catch (PDOException $e) {
    echo "<script>
            alert('GAGAL! Error: " . addslashes($e->getMessage()) . "');
            window.location='list_anggota.php';
          </script>";
}
?>