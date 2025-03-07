<?php
session_start();
require_once '../setting/koneksi.php';

header('Content-Type: application/json');

if(!isset($_SESSION['login_user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

if(!isset($_POST['id_rating']) || !isset($_POST['balasan']) || empty($_POST['balasan'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

$id_rating = (int)$_POST['id_rating'];
$balasan = trim($_POST['balasan']);
$username = $_SESSION['login_user'];

try {
    // Dapatkan id_t_anggota dari username
    $sql_user = "SELECT id_t_anggota, nama FROM t_anggota WHERE username = ?";
    $stmt_user = mysqli_prepare($db, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "s", $username);
    mysqli_stmt_execute($stmt_user);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

    if(!$user) {
        throw new Exception('User tidak ditemukan');
    }

    // Insert balasan
    $sql = "INSERT INTO t_rating_balasan (id_rating, balasan, create_by, create_date) 
            VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $id_rating, $balasan, $user['id_t_anggota']);
    mysqli_stmt_execute($stmt);

    if(mysqli_affected_rows($db) > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Balasan berhasil ditambahkan',
            'nama' => $user['nama'],
            'balasan' => nl2br(htmlspecialchars($balasan)),
            'tanggal' => date('d M Y H:i')
        ]);
    } else {
        throw new Exception('Gagal menambahkan balasan');
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 