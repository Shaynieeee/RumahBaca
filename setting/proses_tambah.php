<?php

if(isset($_POST['tambah_buku'])) {
    $judul = $_POST['judul'];
    $isbn = $_POST['isbn'];
    $pengarang = $_POST['pengarang'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $bahasa = $_POST['bahasa'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $deskripsi = $_POST['deskripsi'];
    $kata_kunci = $_POST['kata_kunci'];
    $preview_content = $_POST['preview_content'];
    
    // Proses upload gambar
    // ... existing upload code ...
    
    $query = "INSERT INTO buku (judul, isbn, pengarang, penerbit, tahun_terbit, 
              bahasa, kategori, jumlah, deskripsi, kata_kunci, preview_content, gambar) 
              VALUES ('$judul', '$isbn', '$pengarang', '$penerbit', '$tahun_terbit',
              '$bahasa', '$kategori', '$jumlah', '$deskripsi', '$kata_kunci', 
              '$preview_content', '$nama_file_baru')";
    // ... existing code ...
} 