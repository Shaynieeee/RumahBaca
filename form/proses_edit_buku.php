<?php
session_start();
require_once '../setting/koneksi.php';

// Cek apakah user sudah login
if(!isset($_SESSION['login_user'])) {
    header("location: ../login.php");
    exit();
}

// Fungsi untuk debugging
function debug_to_file($message) {
    file_put_contents('../debug_upload.log', date('Y-m-d H:i:s') . ': ' . print_r($message, true) . "\n", FILE_APPEND);
}

// Cek apakah ada data yang dikirim
if(isset($_POST['submit'])) {
    // Ambil data dari form
    $id_buku = $_POST['id_buku'];
    $nama_buku = $_POST['nama_buku'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $jenis = $_POST['jenis'];
    $deskripsi = $_POST['deskripsi'];
    $username = $_SESSION['login_user'];
    
    // Debug data yang diterima
    debug_to_file("Data POST: " . print_r($_POST, true));
    debug_to_file("Data FILES: " . print_r($_FILES, true));
    
    // Cek apakah ada file gambar yang diupload
    $gambar_lama = $_POST['gambar_lama'];
    $upload_gambar = false;
    $nama_gambar = $gambar_lama;
    
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_gambar = true;
        $file_gambar = $_FILES['gambar'];
        $nama_file = $file_gambar['name'];
        $ukuran_file = $file_gambar['size'];
        $tmp_file = $file_gambar['tmp_name'];
        
        // Cek ekstensi file
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        
        // Cek ekstensi file
        if(!in_array($ekstensi, $ekstensi_diperbolehkan)) {
            header("location: edit_buku.php?id=$id_buku&error=ekstensi");
            exit();
        }
        
        // Cek ukuran file (maksimal 2 MB)
        if($ukuran_file > 2097152) {
            header("location: edit_buku.php?id=$id_buku&error=ukuran");
            exit();
        }
        
        // Buat nama file dengan timestamp
        $timestamp = time();
        $nama_gambar = $timestamp;
        
        // Upload file dengan nama timestamp_Screenshot-YYYY-MM-DD-HHMMSS.ext
        $nama_file_lengkap = $timestamp . '_Screenshot-' . date('Y-m-d-His') . '.' . $ekstensi;
        
        // Upload file
        $upload_dir = '../image/buku/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $nama_file_lengkap;
        
        debug_to_file("Uploading image: $tmp_file to $upload_path");
        
        if(!move_uploaded_file($tmp_file, $upload_path)) {
            debug_to_file("Failed to upload file");
            header("location: edit_buku.php?id=$id_buku&error=upload");
            exit();
        }
        
        debug_to_file("File uploaded successfully. DB name: $nama_gambar, File name: $nama_file_lengkap");
        
        // Hapus gambar lama jika ada dan bukan default
        if($gambar_lama != 'default.jpg' && !empty($gambar_lama)) {
            // Cari file yang dimulai dengan nama gambar lama di direktori
            $files = glob($upload_dir . $gambar_lama . '*');
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                    debug_to_file("Deleted old file: $file");
                }
            }
        }
    }
    
    // Cek apakah ada file PDF yang diupload
    $file_buku_lama = $_POST['file_buku_lama'] ?? '';
    $upload_pdf = false;
    $nama_pdf = $file_buku_lama;
    
    if(isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0) {
        // ... kode untuk upload PDF (tidak diubah) ...
    }
    
    // Update data buku
    $sql = "UPDATE t_buku SET 
            nama_buku = ?, 
            penulis = ?, 
            penerbit = ?, 
            tahun_terbit = ?, 
            jenis = ?, 
            deskripsi = ?, 
            update_by = ?, 
            update_date = NOW()";
    
    // Tambahkan kolom gambar jika ada upload gambar
    if($upload_gambar) {
        $sql .= ", gambar = ?";
    }
    
    // Tambahkan kolom file_buku jika ada upload PDF
    if($upload_pdf) {
        $sql .= ", file_buku = ?";
    }
    
    $sql .= " WHERE id_t_buku = ?";
    
    $stmt = mysqli_prepare($db, $sql);
    
    // Bind parameter sesuai dengan query
    if($upload_gambar && $upload_pdf) {
        mysqli_stmt_bind_param($stmt, "sssssssss", $nama_buku, $penulis, $penerbit, $tahun_terbit, $jenis, $deskripsi, $username, $nama_gambar, $nama_pdf, $id_buku);
    } elseif($upload_gambar) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $nama_buku, $penulis, $penerbit, $tahun_terbit, $jenis, $deskripsi, $username, $nama_gambar, $id_buku);
    } elseif($upload_pdf) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $nama_buku, $penulis, $penerbit, $tahun_terbit, $jenis, $deskripsi, $username, $nama_pdf, $id_buku);
    } else {
        mysqli_stmt_bind_param($stmt, "sssssss", $nama_buku, $penulis, $penerbit, $tahun_terbit, $jenis, $deskripsi, $username, $id_buku);
    }
    
    // Eksekusi query
    if(mysqli_stmt_execute($stmt)) {
        debug_to_file("Database updated successfully");
        // Redirect ke halaman data buku dengan pesan sukses
        header("location: data_buku.php?success=edit");
        exit();
    } else {
        debug_to_file("Database error: " . mysqli_error($db));
        // Redirect ke halaman edit buku dengan pesan error
        header("location: edit_buku.php?id=$id_buku&error=database&msg=" . mysqli_error($db));
        exit();
    }
} else {
    // Redirect ke halaman data buku jika tidak ada data yang dikirim
    header("location: data_buku.php");
    exit();
}
?> 