<?php
session_start();
require_once '../setting/koneksi.php';

if (!isset($scopes) || !(in_array('buku', $scopes))) {
	header("Location: ../../landing.php");
	exit;
}

echo "<h1>Perbaikan Path Gambar</h1>";

// Ambil semua data buku
$sql = "SELECT id_t_buku, gambar FROM t_buku";
$result = mysqli_query($db, $sql);

$fixed = 0;
$total = mysqli_num_rows($result);

echo "<p>Total buku: $total</p>";
echo "<ul>";

while($row = mysqli_fetch_assoc($result)) {
    $id_buku = $row['id_t_buku'];
    $gambar_id = $row['gambar'];
    
    echo "<li>Buku ID: $id_buku, Gambar: $gambar_id - ";
    
    if(empty($gambar_id) || $gambar_id == 'default.jpg') {
        echo "Tidak perlu perbaikan (default)</li>";
        continue;
    }
    
    // Cek apakah file ada
    $pattern = "../image/buku/{$gambar_id}*";
    $files = glob($pattern);
    
    if(!empty($files)) {
        echo "File ditemukan: " . basename($files[0]) . "</li>";
        continue;
    }
    
    // Jika tidak ditemukan, coba cari dengan pola lain
    $pattern2 = "../image/buku/*{$gambar_id}*";
    $files2 = glob($pattern2);
    
    if(!empty($files2)) {
        echo "File ditemukan dengan pola alternatif: " . basename($files2[0]) . "</li>";
        continue;
    }
    
    echo "File tidak ditemukan!</li>";
}

echo "</ul>";
echo "<p>Selesai memeriksa data.</p>";
?> 