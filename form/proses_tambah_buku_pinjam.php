<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../setting/koneksi.php';
require_once '../setting/session.php';

header('Content-Type: application/json');
$response = array();

if(!isset($_SESSION['login_user'])) {
    header("location:../index.php");
    exit;
}

$usersession = $_SESSION['login_user'];

// Cek role dari t_account
$sql_role = "SELECT a.id_p_role, COALESCE(s.id_t_staff, 0) as id_staff 
             FROM t_account a 
             LEFT JOIN t_staff s ON a.id_t_account = s.id_t_account 
             WHERE a.username = '$usersession'";
$result_role = mysqli_query($db, $sql_role);
$row_role = mysqli_fetch_assoc($result_role);

if (!$row_role) {
    echo "<script>
            alert('User tidak ditemukan!');
            window.location.href='input_peminjaman.php';
          </script>";
    exit;
}

$role_id = $row_role['id_p_role'];
$id_staff = $row_role['id_staff'];

if(isset($_POST['submit'])) {
    try {
        mysqli_begin_transaction($db);
        
        $id_anggota = $_POST['id_anggota'];
        $no_peminjaman = $_POST['no_peminjaman'];
        $tgl_pinjam = $_POST['tgl_pinjam'];
        $tgl_kembali = $_POST['tgl_kembali'];
        $id_t_staff = $_POST['id_t_staff'];
        $buku_array = json_decode($_POST['buku']);
        $user = $_SESSION['login_user'];
        
        // Insert ke t_peminjaman
        $sql_pinjam = "INSERT INTO t_peminjaman (no_peminjaman, id_t_anggota, id_t_staff, 
                       tgl_pinjam, tgl_kembali, status, create_by, create_date) 
                       VALUES (?, ?, ?, ?, ?, 'Dipinjam', ?, NOW())";
        $stmt = mysqli_prepare($db, $sql_pinjam);
        mysqli_stmt_bind_param($stmt, "siisss", $no_peminjaman, $id_anggota, $id_t_staff,
                             $tgl_pinjam, $tgl_kembali, $user);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting peminjaman: " . mysqli_error($db));
        }
        
        $id_peminjaman = mysqli_insert_id($db);
        
        // Insert setiap buku ke t_detil_pinjam
        foreach($buku_array as $id_buku) {
            // Cek stok buku
            $sql_stok = "SELECT stok FROM t_buku WHERE id_t_buku = ? AND stok > 0";
            $stmt_stok = mysqli_prepare($db, $sql_stok);
            mysqli_stmt_bind_param($stmt_stok, "i", $id_buku);
            mysqli_stmt_execute($stmt_stok);
            $result_stok = mysqli_stmt_get_result($stmt_stok);
            
            if(mysqli_num_rows($result_stok) > 0) {
                // Insert detail peminjaman
                $sql_detail = "INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, 
                              kondisi, create_by, create_date) 
                              VALUES (?, ?, 'Baik', ?, NOW())";
                $stmt_detail = mysqli_prepare($db, $sql_detail);
                mysqli_stmt_bind_param($stmt_detail, "iis", $id_peminjaman, 
                                     $id_buku, $user);
                
                if (!mysqli_stmt_execute($stmt_detail)) {
                    throw new Exception("Error inserting detail: " . mysqli_error($db));
                }
                
                // Update stok buku
                $sql_update = "UPDATE t_buku SET stok = stok - 1 WHERE id_t_buku = ?";
                $stmt_update = mysqli_prepare($db, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "i", $id_buku);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception("Error updating stock: " . mysqli_error($db));
                }
            }
        }
        
        mysqli_commit($db);
        $response['status'] = 'success';
        $response['message'] = 'Data peminjaman berhasil disimpan';
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?> 