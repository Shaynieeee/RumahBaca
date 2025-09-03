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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <style>
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
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .hero-section p {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 1.5rem;
            color: rgb(255, 255, 255);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
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
                        <a class="nav-link" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/public/catalog.php">Katalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://pelindo.co.id/port/pelabuhan-tanjung-perak"
                            target="_blank">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary px-3 text-light"
                            href="<?php echo BASE_URL; ?>/login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <center>
        <div class="rumah-baca-title">
            <h1 class="display-4 fw-bold">Rumah Baca</h1>
            <p class="subtitle">Perpustakaan Digital Pelindo</p>
        </div>
    </center>
    <style>
        /* Styling untuk judul Rumah Baca */
        .rumah-baca-title {
            margin: 30px 0;
            padding: 15px;
        }

        .rumah-baca-title h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: #0067b0;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            letter-spacing: -0.5px;
            transition: all 0.3s ease;
        }

        .rumah-baca-title h1:hover {
            transform: scale(1.02);
            color: #005091;
        }

        .rumah-baca-title .subtitle {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .rumah-baca-title h1 {
                font-size: 2.5rem;
            }

            .rumah-baca-title .subtitle {
                font-size: 1rem;
            }
        }
    </style>
    <!-- Hero Section -->
    <section class="hero-section">
        <center>
            <div id="carouselExampleAutoplaying" class="carousel slide carousel-fade" data-bs-ride="carousel"
                data-bs-interval="5000">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="../image/gambar1.jpeg" class="d-block"
                            style="width:100%; height: 500px; object-fit: cover;" alt="...">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Selamat Datang di Rumah Baca</h5>
                            <p>Temukan berbagai koleksi buku menarik</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="../image/gambar2.jpg" class="d-block"
                            style="width:100%; height: 500px; object-fit: cover;" alt="...">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Baca Dimana Saja</h5>
                            <p>Akses koleksi buku digital kapan saja dan dimana saja</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="../image/gambar3.jpg" class="d-block"
                            style="width:100%; height: 500px; object-fit: cover;" alt="...">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Tingkatkan Pengetahuan</h5>
                            <p>Perluas wawasan dengan berbagai kategori buku</p>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="0"
                        class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide-to="2"
                        aria-label="Slide 3"></button>
                </div>
            </div>
        </center>
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
    <div class="container mt-5">
        <div class="row justify-content-center text-center">
            <div class="col-md-15">
                <h1 class="display-4 mb-4">Temukan Buku Favoritmu</h1>
                <p class="lead mb-5">Akses ribuan koleksi buku digital dengan mudah</p>

                <!-- Search Box -->
                <div class="search-box">
                    <form action="<?php echo BASE_URL; ?>/public/catalog.php" method="GET">
                        <div class="row">
                            <div class="col-md-4 mb-3" style="width: 100%;">
                                <input type="text" name="keyword" class="form-control form-control-lg"
                                    placeholder="Cari judul, pengarang..."
                                    value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <select name="jenis" class="form-control form-control-lg">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    // Gunakan tabel t_kategori_buku untuk kategori
                                    $query = "SELECT nama_kategori FROM t_kategori_buku ORDER BY nama_kategori";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $sel = (isset($_GET['jenis']) && $_GET['jenis'] == $row['nama_kategori']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['nama_kategori']) . "' $sel>" . htmlspecialchars($row['nama_kategori']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <select name="ketersediaan" class="form-control form-control-lg">
                                    <option value="">Semua Ketersediaan</option>
                                    <option value="online" <?php echo (isset($_GET['ketersediaan']) && $_GET['ketersediaan'] == 'online') ? 'selected' : ''; ?>>Buku Online</option>
                                    <option value="offline" <?php echo (isset($_GET['ketersediaan']) && $_GET['ketersediaan'] == 'offline') ? 'selected' : ''; ?>>Buku Offline</option>
                                    <option value="both" <?php echo (isset($_GET['ketersediaan']) && $_GET['ketersediaan'] == 'both') ? 'selected' : ''; ?>>Online & Offline</option>
                                </select>
                            </div>

                            <!-- ADDED: stok minimum input -->
                            <div class="col-md-2 mb-3">
                                <input type="number" min="0" name="stok" class="form-control form-control-lg"
                                    placeholder="Min. stok"
                                    value="<?php echo isset($_GET['stok']) ? (int) $_GET['stok'] : ''; ?>">
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
    <!-- Buku Terbaru -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Buku Terbaru</h2>
                <a href="<?php echo BASE_URL; ?>/public/catalog.php" class="btn btn-outline-primary">Lihat Semua</a>
            </div>

            <div class="row">
                <?php
                $catalog_mode = "Terbaru";
                include $root . '/components/catalog/catalog.php';
                $query = "SELECT * FROM t_buku ORDER BY create_date DESC LIMIT 4";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <?php
                            // Cari file gambar yang sesuai
                            $gambar_id = $row['gambar'] ?? '';
                            $gambar_path = "../image/buku/default.jpg"; // Default image
                        
                            if (!empty($gambar_id)) {
                                // Coba cari file dengan pola nama yang sesuai
                                $files = glob("../image/buku/{$gambar_id}*");
                                if (!empty($files)) {
                                    $gambar_path = $files[0]; // Ambil file pertama yang ditemukan
                                } else {
                                    // Jika tidak ditemukan, coba cari dengan pola lain
                                    $files2 = glob("../image/buku/*{$gambar_id}*");
                                    if (!empty($files2)) {
                                        $gambar_path = $files2[0];
                                    }
                                }
                            }
                            ?>
                            <img src="<?php echo $gambar_path; ?>" class="card-img-top"
                                alt="<?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>"
                                style="height: 250px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-truncate">
                                    <?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>
                                </h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fa fa-user"></i>
                                        <?php echo htmlspecialchars($row['penulis'] ?? 'Tidak ada penulis'); ?><br>
                                        <i class="fa fa-calendar"></i>
                                        <?php echo htmlspecialchars($row['tahun_terbit'] ?? 'Tidak ada tahun'); ?>
                                    </small>
                                </p>
                                <div class="mt-2">
                                    <?php if (!empty($row['file_buku'])): ?>
                                        <?php if ($row['stok'] > 0): ?>
                                            <span class="badge badge-success">Tersedia Offline</span>
                                            <span class="badge badge-info">Stok buku : <?php echo (int) $row['stok']; ?> </span>
                                            <span class="badge badge-info">Tersedia Online</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Tersedia Hanya Online</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($row['stok'] > 0): ?>
                                            <span class="badge badge-success">Tersedia Offline</span>
                                            <span class="badge badge-info">Stok buku : <?php echo (int) $row['stok']; ?> </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Tidak Tersedia</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
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
                        <li><a href="https://pelindo.co.id/port/pelabuhan-tanjung-perak" class="text-light"
                                target="_blank">Tentang Kami</a></li>
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