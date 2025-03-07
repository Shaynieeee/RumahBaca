<?php
// Gunakan __DIR__ untuk mendapatkan path yang benar
$root = dirname(__DIR__);
include $root . '/setting/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Baca</title>
    
    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                        url('https://source.unsplash.com/1600x900/?library');
            background-size: cover;
            background-position: center;
            min-height: 500px;
            color: white;
            padding: 100px 0;
        }
        
        .search-box {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
        <img src="../public/assets/pelindo-logo.png" alt="Logo Pelindo" height="30" class="d-inline-block align-middle me-2">
         Rumah Baca
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/public/catalog.php">Katalog</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="#">Tentang</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link btn btn-primary px-3 text-light" href="<?php echo BASE_URL; ?>/login.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-md-8">
                <h1 class="display-4 mb-4">Temukan Buku Favoritmu</h1>
                <p class="lead mb-5">Akses ribuan koleksi buku digital dengan mudah</p>
                
                <!-- Search Box -->
                <div class="search-box">
                    <form action="<?php echo BASE_URL; ?>/public/catalog.php" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="keyword" class="form-control form-control-lg" 
                                       placeholder="Cari judul, pengarang...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <select name="jenis" class="form-control form-control-lg">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    $query = "SELECT DISTINCT jenis FROM t_buku ORDER BY jenis";
                                    $result = mysqli_query($conn, $query);
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='".$row['jenis']."'>".$row['jenis']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Section -->
<!-- <section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Kategori Populer</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="category-box text-center">
                    <i class="fas fa-laptop fa-2x mb-3 text-primary"></i>
                    <h5>Komputer</h5>
                    <p class="text-muted mb-0">50+ Buku</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="category-box text-center">
                    <i class="fas fa-book fa-2x mb-3 text-success"></i>
                    <h5>Novel</h5>
                    <p class="text-muted mb-0">100+ Buku</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="category-box text-center">
                    <i class="fas fa-chart-line fa-2x mb-3 text-info"></i>
                    <h5>Bisnis</h5>
                    <p class="text-muted mb-0">30+ Buku</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="category-box text-center">
                    <i class="fas fa-brain fa-2x mb-3 text-warning"></i>
                    <h5>Pengembangan Diri</h5>
                    <p class="text-muted mb-0">40+ Buku</p>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- Buku Terbaru -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Buku Terbaru</h2>
            <a href="<?php echo BASE_URL; ?>/public/catalog.php" class="btn btn-outline-primary">Lihat Semua</a>
        </div>
        
        <div class="row">
            <?php
            $query = "SELECT * FROM t_buku ORDER BY create_date DESC LIMIT 4";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_assoc($result)) {
            ?>
           <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <img src="../image/buku/<?php echo htmlspecialchars($row['gambar'] ?? 'default.jpg'); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>"
                                             style="height: 250px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($row['penulis'] ?? 'Tidak ada penulis'); ?><br>
                                                    <i class="fa fa-calendar"></i> <?php echo htmlspecialchars($row['tahun_terbit'] ?? 'Tidak ada tahun'); ?>
                                                </small>
                                            </p>
                                            <div class="btn-group mt-auto w-100">
                                                <a href="detail-buku.php?id=<?php echo $row['id_t_buku']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fa fa-info-circle"></i> Detail
                                                </a>    
                                            </div>
                                        </div>
                                    </div>
                                </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Rumah Baca</h5>
                <p class="text-muted">Akses ribuan koleksi buku digital dengan mudah dan cepat.</p>
            </div>
            <div class="col-md-4">
                <h5>Link Cepat</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted">Beranda</a></li>
                    <li><a href= "<?php echo BASE_URL; ?>/public/catalog.php" class="text-muted">Katalog</a></li>
                    <!-- <li><a href="#" class="text-muted">Tentang Kami</a></li> -->
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Hubungi Kami</h5>
                <p class="text-muted">
                    <i class="fas fa-envelope mr-2"></i> info@perpustakaan.com<br>
                    <i class="fas fa-phone mr-2"></i> (021) 1234567<br>
                    <i class="fas fa-map-marker-alt mr-2"></i> Jl. Perpustakaan No. 1
                </p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center text-muted">
            <small>&copy; 2025 Rumah Baca. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 