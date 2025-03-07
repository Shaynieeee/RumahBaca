<?php
session_start();
require_once '../../setting/koneksi.php';

// Fungsi untuk menulis log
function writeLog($message) {
    $log_file = fopen("rating_debug.log", "a");
    fwrite($log_file, date('[Y-m-d H:i:s] ') . $message . "\n");
    fclose($log_file);
}

if(!isset($_SESSION['login_user'])) {
    header("Location: ../../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    writeLog("Memulai proses rating");
    writeLog("POST data: " . print_r($_POST, true));
    writeLog("SESSION data: " . print_r($_SESSION, true));
    
    $username = $_SESSION['login_user'];
    
    // Query yang benar langsung ke t_account
    $sql_anggota = "SELECT id_t_anggota 
                    FROM t_account 
                    WHERE username = ? 
                    AND id_t_anggota IS NOT NULL";
    
    writeLog("Query anggota: " . $sql_anggota);
    
    $stmt = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result_anggota = mysqli_stmt_get_result($stmt);
    
    if(!$result_anggota || mysqli_num_rows($result_anggota) == 0) {
        writeLog("Error: Data anggota tidak ditemukan");
        $_SESSION['error'] = "Data anggota tidak ditemukan";
        header("Location: detail-buku.php?id=" . $_POST['id_buku']);
        exit();
    }

    $anggota = mysqli_fetch_assoc($result_anggota);
    writeLog("Data anggota ditemukan: " . print_r($anggota, true));
    
    $id_anggota = $anggota['id_t_anggota'];
    $id_buku = (int)$_POST['id_buku'];
    $rating = (int)$_POST['rating'];
    $ulasan = mysqli_real_escape_string($db, $_POST['ulasan']);
    
    try {
        // Insert rating baru
        $sql = "INSERT INTO t_rating_buku 
                (id_t_buku, id_t_anggota, rating, ulasan, created_date) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iiis", $id_buku, $id_anggota, $rating, $ulasan);
        
        writeLog("Mencoba menyimpan rating");
        
        if(mysqli_stmt_execute($stmt)) {
            writeLog("Rating berhasil disimpan");
            $_SESSION['success'] = "Rating dan ulasan berhasil disimpan";
        } else {
            throw new Exception(mysqli_error($db));
        }
        
    } catch (Exception $e) {
        writeLog("Error: " . $e->getMessage());
        $_SESSION['error'] = "Gagal menyimpan rating: " . $e->getMessage();
    }
    
    header("Location: detail-buku.php?id=" . $id_buku);
    exit();
}
?> 