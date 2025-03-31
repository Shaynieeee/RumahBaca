<?php
require_once '../config/connection.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    mysqli_begin_transaction($db);
    
    try {
        // Ambil id_t_account dari anggota
        $sql_get_account = "SELECT id_t_account FROM t_anggota WHERE id_t_anggota = ?";
        $stmt = mysqli_prepare($db, $sql_get_account);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $id_t_account = $row['id_t_account'];
        
        // Hapus data dari t_anggota
        $sql_delete_anggota = "DELETE FROM t_anggota WHERE id_t_anggota = ?";
        $stmt = mysqli_prepare($db, $sql_delete_anggota);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting member: " . mysqli_error($db));
        }
        
        // Hapus account jika ada
        if($id_t_account) {
            $sql_delete_account = "DELETE FROM t_account WHERE id_t_account = ?";
            $stmt = mysqli_prepare($db, $sql_delete_account);
            mysqli_stmt_bind_param($stmt, "i", $id_t_account);
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error deleting account: " . mysqli_error($db));
            }
        }
        
        mysqli_commit($db);
        echo "<script>
                alert('Data berhasil dihapus!');
                window.location='data_anggota.php';
              </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location='data_anggota.php';
              </script>";
    }
} else {
    header("Location: data_anggota.php");
    exit;
}

?>