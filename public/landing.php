<?php
// Gunakan __DIR__ untuk mendapatkan path yang benar
$root = dirname(__DIR__);
$catalog_mode = "dashboard_guest";
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

    <div class="container mt-5">
        <div class="row justify-content-center text-center">
            <div class="col-md-15">
                <h1 class="display-4 mb-4">Temukan Buku Favoritmu</h1>
                <p class="lead mb-5">Akses ribuan koleksi buku digital dengan mudah</p>

                <?php
                include $root . '/components/catalog/catalog.php';
                ?>

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
                                    <li><a href="<?php echo BASE_URL; ?>/public/catalog.php"
                                            class="text-light">Katalog</a></li>
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