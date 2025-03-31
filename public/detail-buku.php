<?php
session_start(); // Tambahkan ini di awal file
$root = dirname(__DIR__);
include $root . '/setting/koneksi.php';

// Validasi ID buku
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/public/catalog.php");
    exit();
}

$id_buku = (int)$_GET['id'];

// Query untuk detail buku
$query = "SELECT * FROM t_buku WHERE id_t_buku = $id_buku";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: " . BASE_URL . "/public/catalog.php");
    exit();
}

$buku = mysqli_fetch_assoc($result);

// Query untuk rating
$rating_query = "SELECT COALESCE(AVG(rating), 0) as avg_rating, 
                 COUNT(*) as total_rating 
                 FROM t_rating_buku 
                 WHERE id_t_buku = $id_buku";
$rating_result = mysqli_query($conn, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = round($rating_data['avg_rating'], 1);

// Query untuk ulasan
$reviews_query = "SELECT r.*, a.nama 
                 FROM t_rating_buku r 
                 JOIN t_anggota a ON r.id_t_anggota = a.id_t_anggota 
                 WHERE r.id_t_buku = $id_buku 
                 ORDER BY r.created_date DESC";
$reviews_result = mysqli_query($conn, $reviews_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $buku['nama_buku']; ?> - Perpustakaan Digital</title>

        <!-- Multiple favicon sizes -->
        <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .rating-stars {
            color: #ffc107;
        }
        .review-card {
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
        }

                /* .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('assets/bg-library.webp');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 0;
        } */
        @import url("https://fonts.googleapis.com/css?family=Poppins:100,300,400,500,600,700,800, 800i, 900&display=swap");
        .hero-content {
            text-align: center;
            color: #ffffff;
        }
        
        .hero-section h1 {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 4rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        
        .hero-section p {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 1.5rem;
            color:rgb(255, 255, 255);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
            margin-bottom: 30px;
            font-weight: 500;
        }
        
        .hero-content {
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        
        .search-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-top: 30px;
        }
        
        .book-card {
            transition: transform 0.3s;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
        }
        
        .category-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .category-box:hover {
            background: #e9ecef;
            cursor: pointer;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 15px 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .brand-logo {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }

        .brand-text {
            color: #ffffff;
            font-family: 'poppins';
            font-size: 70px;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        /* .navbar-nav .nav-link {
            color: #ecf0f1;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0 10px;
        } */

        /* .navbar-nav .nav-link:hover {
            color: #3498db;
        } */

        .btn-login {
            background-color: #3498db;
            color: #ffffff;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .btn-login:hover {
            background-color: #2980b9;
            color: #ffffff;
        }

        /* CSS untuk carousel */
        .carousel-fade .carousel-item {
            opacity: 0;
            transition-duration: 1s;
            transition-property: opacity;
        }
        
        .carousel-fade .carousel-item.active {
            opacity: 1;
        }
        
        .carousel-fade .carousel-item-next.carousel-item-start,
        .carousel-fade .carousel-item-prev.carousel-item-end {
            opacity: 1;
        }
        
        .carousel-fade .active.carousel-item-start,
        .carousel-fade .active.carousel-item-end {
            opacity: 0;
        }
        
        .carousel-fade .carousel-item-next,
        .carousel-fade .carousel-item-prev,
        .carousel-fade .carousel-item.active,
        .carousel-fade .active.carousel-item-start,
        .carousel-fade .active.carousel-item-end {
            transform: translateX(0);
            transform: translate3d(0, 0, 0);
        }
        
        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
        }
        
        .carousel-indicators {
            bottom: 20px;
        }
        
        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }

        .badge {
            padding: 6px 10px;
            margin: 2px;
            font-size: 12px;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-danger {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="assets/logo-rumahbaca.png" alt="Logo" class="brand-logo">
            <span class="brand-text"></span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                <a class="nav-link" href="<?php echo BASE_URL; ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/public/catalog.php">Katalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://pelindo.co.id/port/pelabuhan-tanjung-perak" target="_blank">Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-primary px-3 text-light" href="<?php echo BASE_URL; ?>/login.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row">
        <!-- Kolom Kiri - Gambar dan Rating Summary -->
        <div class="col-md-4">
            <?php
            // Cari file gambar yang sesuai
            $gambar_id = $buku['gambar'] ?? '';
            $gambar_path = "../image/buku/default.jpg"; // Default image
            
            if(!empty($gambar_id)) {
                // Coba cari file dengan pola nama yang sesuai
                $files = glob("../image/buku/{$gambar_id}*");
                if(!empty($files)) {
                    $gambar_path = $files[0]; // Ambil file pertama yang ditemukan
                } else {
                    // Jika tidak ditemukan, coba cari dengan pola lain
                    $files2 = glob("../image/buku/*{$gambar_id}*");
                    if(!empty($files2)) {
                        $gambar_path = $files2[0];
                    }
                }
            }
            ?>
            <img src="<?php echo $gambar_path; ?>" class="img-fluid rounded" alt="Cover Buku">
            
            <!-- Rating Summary Box -->
            <div class="card-body text-center">
                <h5 class="card-title">Rating Rata-rata</h5>
                <div class="display-4 font-weight-bold text-warning mb-2">
                    <?php echo number_format($avg_rating, 1); ?>
                </div>
                <div class="rating-stars mb-2">
                    <?php
                    for($i = 1; $i <= 5; $i++) {
                        if($i <= $avg_rating) {
                            echo '<i class="fas fa-star fa-lg text-warning"></i>';
                        } else {
                            echo '<i class="far fa-star fa-lg text-warning"></i>';
                        }
                    }
                    ?>
                </div>
                <p class="text-muted mb-0">
                    Berdasarkan <?php echo $rating_data['total_rating']; ?> ulasan
                </p>
            </div>
        </div>

        <!-- Kolom Kanan - Detail dan Ulasan -->
        <div class="col-md-8">
            <!-- Detail Buku -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title mb-3"><?php echo $buku['nama_buku']; ?></h2>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-user-edit text-primary mr-2"></i>
                                <strong>Penulis:</strong> <?php echo $buku['penulis']; ?>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-building text-primary mr-2"></i>
                                <strong>Penerbit:</strong> <?php echo $buku['penerbit']; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                <strong>Tahun Terbit:</strong> <?php echo $buku['tahun_terbit']; ?>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-bookmark text-primary mr-2"></i>
                                <strong>Kategori:</strong> <?php echo $buku['jenis']; ?>
                            </p>
                        </div>
                    </div>
                    <p class="card-text"><?php echo $buku['sinopsis']; ?></p>
                </div>
            </div>

            <!-- Form Rating (Hanya untuk user yang login) -->
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-star text-warning mr-2"></i>
                        Berikan Rating & Ulasan
                    </h5>
                    <form action="<?php echo BASE_URL; ?>/process/submit_rating.php" method="POST">
                        <input type="hidden" name="id_buku" value="<?php echo $id_buku; ?>">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Rating</label>
                            <div class="rating-input d-flex justify-content-between">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="rating<?php echo $i; ?>" 
                                           name="rating" value="<?php echo $i; ?>" 
                                           class="custom-control-input" required>
                                    <label class="custom-control-label" for="rating<?php echo $i; ?>">
                                        <?php echo $i; ?> <i class="fas fa-star text-warning"></i>
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Ulasan Anda</label>
                            <textarea name="ulasan" class="form-control" rows="3" 
                                    placeholder="Bagikan pendapat Anda tentang buku ini..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Kirim Ulasan
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Daftar Ulasan -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-comments text-primary mr-2"></i>
                        Ulasan Pembaca
                    </h5>
                    
                    <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                        <div class="review-card border-left border-primary pl-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 font-weight-bold"><?php echo $review['nama']; ?></h6>
                                <div class="rating-stars">
                                    <?php
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $review['rating']) {
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="far fa-star text-warning"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <p class="mb-1"><?php echo $review['ulasan']; ?></p>
                            <small class="text-muted">
                                <i class="far fa-clock mr-1"></i>
                                <?php echo date('d M Y', strtotime($review['created_date'])); ?>
                            </small>

                            <!-- Tambah like/dislike counter -->
                            <div class="d-flex align-items-center mt-2">
                                <span class="mr-3">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    <span id="like-count-<?php echo $review['id_rating']; ?>">
                                        <?php
                                        $sql_likes = "SELECT COUNT(*) as count FROM t_rating_like 
                                                     WHERE id_rating = ? AND jenis = 'like'";
                                        $stmt_likes = mysqli_prepare($conn, $sql_likes);
                                        mysqli_stmt_bind_param($stmt_likes, "i", $review['id_rating']);
                                        mysqli_stmt_execute($stmt_likes);
                                        $like_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_likes))['count'];
                                        echo $like_count;
                                        ?>
                                    </span>
                                </span>
                                <span class="mr-3">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    <span id="dislike-count-<?php echo $review['id_rating']; ?>">
                                        <?php
                                        $sql_dislikes = "SELECT COUNT(*) as count FROM t_rating_like 
                                                        WHERE id_rating = ? AND jenis = 'dislike'";
                                        $stmt_dislikes = mysqli_prepare($conn, $sql_dislikes);
                                        mysqli_stmt_bind_param($stmt_dislikes, "i", $review['id_rating']);
                                        mysqli_stmt_execute($stmt_dislikes);
                                        $dislike_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_dislikes))['count'];
                                        echo $dislike_count;
                                        ?>
                                    </span>
                                </span>
                            </div>

                            <!-- Daftar balasan -->
                            <div class="replies-list ml-4 mt-3">
                                <?php
                                // Perbaiki query untuk menggunakan t_rating_balasan
                                $sql_replies = "SELECT b.*, a.nama 
                                               FROM t_rating_balasan b 
                                               JOIN t_anggota a ON b.create_by = a.id_t_anggota 
                                               WHERE b.id_rating = ? 
                                               ORDER BY b.create_date ASC";
                                $stmt_replies = mysqli_prepare($conn, $sql_replies);
                                mysqli_stmt_bind_param($stmt_replies, "i", $review['id_rating']);
                                mysqli_stmt_execute($stmt_replies);
                                $replies = mysqli_stmt_get_result($stmt_replies);
                                
                                while($reply = mysqli_fetch_assoc($replies)):
                                ?>
                                    <div class="reply-item border-left border-secondary pl-3 py-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($reply['nama']); ?></strong>
                                            <small class="text-muted">
                                                <?php echo date('d M Y H:i', strtotime($reply['create_date'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['balasan'])); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="far fa-comment-dots fa-3x mb-3"></i>
                            <p class="mb-0">Belum ada ulasan untuk buku ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Setelah bagian ulasan pembaca -->
            <div class="mt-3">
                <a href="<?php echo BASE_URL; ?>/public/catalog.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <?php if(!empty($buku['file_buku'])): ?>
                    <a href="../public/baca-buku.php?id=<?php echo $buku['id_t_buku']; ?>" class="btn btn-primary">
                        <i class="fas fa-book-reader mr-2"></i>Baca Buku
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-light py-4" style="background: #0067b0;">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Kantor Pusat</h5>
                <p class="text-light">PT Pelabuhan Indonesia (Persero)
                Pelindo Tower, Jl. Yos Sudarso No.9, Jakarta Utara 14230</p>
            </div>
            <div class="col-md-4">
                <h5>Link Cepat</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light">Beranda</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/catalog.php" class="text-light">Katalog</a></li>
                    <li><a href="https://pelindo.co.id/port/pelabuhan-tanjung-perak" class="text-light" target="_blank">Tentang Kami</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Hubungi Kami</h5>
                <p class="text-lgiht">
                    <i class="fas fa-envelope mr-2"></i> info@perpustakaan.com<br>
                    <i class="fas fa-phone mr-2"></i> (021) 1234567<br>
                    <i class="fas fa-map-marker-alt mr-2"></i> Jl. Perpustakaan No. 1
                </p>
            </div>
        </div>
        <hr class="bg-light">
        <div class="text-center text-light">
            <small>&copy; 2025 Rumah Baca. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 