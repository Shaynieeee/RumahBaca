<?php
// Tambahkan validasi berdasarkan ketersediaan
$ketersediaan = $_POST['ketersediaan'];

// Validasi input berdasarkan ketersediaan
if ($ketersediaan == 'online' || $ketersediaan == 'both') {
    // Validasi file PDF
    if (empty($_FILES['file_buku']['name']) && empty($buku['file_buku'])) {
        $errors[] = "File PDF wajib diupload untuk buku online";
    }
}

if ($ketersediaan == 'offline' || $ketersediaan == 'both') {
    // Validasi field offline
    if (empty($_POST['stok'])) {
        $errors[] = "Stok wajib diisi untuk buku offline";
    }
    if (empty($_POST['harga'])) {
        $errors[] = "Harga wajib diisi untuk buku offline";
    }
    if (empty($_POST['kode_rak'])) {
        $errors[] = "Kode rak wajib diisi untuk buku offline";
    }
}

// Jika tidak ada error, lanjutkan proses simpan
if (empty($errors)) {
    // Tambahkan field ketersediaan ke database
    $sql = "INSERT INTO t_buku (nama_buku, penulis, penerbit, tahun_terbit, ketersediaan, 
            stok, harga, kode_rak, file_buku) VALUES (...)";
    // Sesuaikan query dengan struktur tabel Anda
} 