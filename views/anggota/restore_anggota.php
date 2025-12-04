<?php
require_once '../../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: list_anggota.php");
    exit();
}

$id = $_GET['id'];

try {
    // Set deleted_at kembali ke NULL (restore data)
    $sql = "UPDATE Anggota SET deleted_at = NULL WHERE id_anggota = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    echo "<script>
            alert('✅ Data anggota berhasil di-RESTORE! Anggota kembali aktif.');
            window.location='list_anggota.php';
          </script>";

} catch (PDOException $e) {
    echo "<script>
            alert('GAGAL Restore! Error: " . addslashes($e->getMessage()) . "');
            window.location='list_anggota.php?show_deleted=1';
          </script>";
}
?>