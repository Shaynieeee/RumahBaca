<?php
$root = dirname(__DIR__);
include $root . '/setting/koneksi.php';

// Pagination setup
$limit = 12; // Jumlah buku per halaman
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter setup
$where = "1=1"; // Base condition
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where .= " AND (nama_buku LIKE '%$keyword%' OR penulis LIKE '%$keyword%')";
}
if (isset($_GET['jenis']) && !empty($_GET['jenis'])) {
    $jenis = mysqli_real_escape_string($conn, $_GET['jenis']);
    $where .= " AND jenis = '$jenis'";
}
if (isset($_GET['ketersediaan']) && !empty($_GET['ketersediaan'])) {
    switch ($_GET['ketersediaan']) {
        case 'online':
            $where .= " AND (file_buku IS NOT NULL AND file_buku != '') ";
            break;
        case 'offline':
            $where .= " AND stok > 0 ";
            break;
        case 'both':
            $where .= " AND ((file_buku IS NOT NULL AND file_buku != '') AND stok > 0) ";
            break;
    }
}

// ADDED: stok minimum filter
if (isset($_GET['stok']) && $_GET['stok'] !== '') {
    $stok_min = (int) $_GET['stok'];
    if ($stok_min > 0) {
        $where .= " AND stok >= $stok_min ";
    }
}

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM t_buku WHERE $where";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Rumah Baca</title>

    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .book-card {
            transition: transform 0.3s;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
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
                    <li class="nav-item active">
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

    <!-- Catalog Section -->

    <div class="container py-5">
        <div class="row">
            <?php
            include $root . '/components/catalog/catalog.php';
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php
                                       echo isset($_GET['keyword']) ? '&keyword=' . urlencode($_GET['keyword']) : '';
                                       echo isset($_GET['jenis']) ? '&jenis=' . urlencode($_GET['jenis']) : '';
                                       echo isset($_GET['ketersediaan']) ? '&ketersediaan=' . urlencode($_GET['ketersediaan']) : '';
                                       echo isset($_GET['stok']) && $_GET['stok'] !== '' ? '&stok=' . urlencode((int) $_GET['stok']) : '';
                                       ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <<!-- Footer -->
        <footer class="text-light py-4" style="background: rgb(0, 103, 176);">
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
                            <li><a href="<?php echo BASE_URL; ?>/public/landing.php" class="text-light">Beranda</a></li>
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