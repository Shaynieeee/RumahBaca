<!-- Bagian untuk menampilkan gambar -->
<div class="col-md-4">
    <?php
    // Cari file gambar yang sesuai
    $gambar_id = $buku['gambar'];
    $gambar_path = "../image/buku/default.jpg"; // Default image
    
    if(!empty($gambar_id)) {
        // Coba cari file dengan pola nama yang sesuai
        $pattern = "../image/buku/{$gambar_id}*";
        $files = glob($pattern);
        
        if(!empty($files)) {
            $gambar_path = $files[0]; // Ambil file pertama yang ditemukan
        } else {
            // Jika tidak ditemukan, coba cari dengan pola lain
            $pattern2 = "../image/buku/*{$gambar_id}*";
            $files2 = glob($pattern2);
            
            if(!empty($files2)) {
                $gambar_path = $files2[0];
            }
        }
    }
    ?>
    <img src="<?php echo $gambar_path; ?>" class="img-fluid rounded" alt="Cover Buku">
</div> 