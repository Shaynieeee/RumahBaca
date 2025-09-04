<?php
session_start();
require_once '../../setting/koneksi.php';

if (!isset($scopes) || !(in_array('downloadBuku-Member', $scopes))) {
	header("Location: login.php");
	exit;
}

// Cek ID buku
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: data_buku.php");
    exit();
}

$id_buku = (int)$_GET['id'];

// Cek apakah tabel t_log_download sudah ada, jika belum maka buat
$sql_check_table = "SHOW TABLES LIKE 't_log_download'";
$result_check_table = mysqli_query($db, $sql_check_table);
if(mysqli_num_rows($result_check_table) == 0) {
    // Tabel belum ada, buat tabel baru
    $sql_create_table = "CREATE TABLE t_log_download (
        id_t_log_download INT AUTO_INCREMENT PRIMARY KEY,
        id_t_anggota INT NOT NULL,
        id_t_buku INT NOT NULL,
        tanggal_download DATETIME NOT NULL,
        FOREIGN KEY (id_t_anggota) REFERENCES t_anggota(id_t_anggota),
        FOREIGN KEY (id_t_buku) REFERENCES t_buku(id_t_buku)
    )";
    mysqli_query($db, $sql_create_table);
}

// Ambil data anggota untuk cek akses download
$username = $_SESSION['login_user'];
$sql_anggota = "SELECT a.* FROM t_anggota a 
                JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
                WHERE acc.username = ?";
$stmt_anggota = mysqli_prepare($db, $sql_anggota);
mysqli_stmt_bind_param($stmt_anggota, "s", $username);
mysqli_stmt_execute($stmt_anggota);
$result_anggota = mysqli_stmt_get_result($stmt_anggota);
$anggota = mysqli_fetch_assoc($result_anggota);

// Cek apakah anggota diizinkan download
if(!$anggota || $anggota['allow_download'] != 1) {
    // Redirect jika tidak diizinkan
    header("location: detail-buku.php?id=$id_buku&error=noaccess");
    exit();
}

// Ambil data file buku
$sql_buku = "SELECT nama_buku, file_buku FROM t_buku WHERE id_t_buku = ?";
$stmt_buku = mysqli_prepare($db, $sql_buku);
mysqli_stmt_bind_param($stmt_buku, "i", $id_buku);
mysqli_stmt_execute($stmt_buku);
$result_buku = mysqli_stmt_get_result($stmt_buku);
$buku = mysqli_fetch_assoc($result_buku);

// Cek apakah buku ada dan memiliki file
if(!$buku || empty($buku['file_buku'])) {
    header("location: detail-buku.php?id=$id_buku&error=nofile");
    exit();
}

// Catat aktivitas download
$id_anggota = $anggota['id_t_anggota'];
$sql_log = "INSERT INTO t_log_download (id_t_anggota, id_t_buku, tanggal_download) 
            VALUES (?, ?, NOW())";
$stmt_log = mysqli_prepare($db, $sql_log);
mysqli_stmt_bind_param($stmt_log, "ii", $id_anggota, $id_buku);
mysqli_stmt_execute($stmt_log);

// Path file PDF
$file_path = "../../image/buku/" . $buku['file_buku'];

// Cek apakah file ada
if(!file_exists($file_path)) {
    header("location: detail-buku.php?id=$id_buku&error=filenotfound");
    exit();
}

// Set header untuk download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $buku['nama_buku'] . '.pdf"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?> 