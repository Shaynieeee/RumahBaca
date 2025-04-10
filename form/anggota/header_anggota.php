<?php
// Cek apakah session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    header("Location: ../../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Baca - Area Anggota</title>
    
          <!-- Multiple favicon sizes -->
          <link rel="icon" type="image/png" sizes="32x32" href="../../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../../public/assets/pelindo-logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .navbar {
            background-color: #343a40;
            padding: 1rem 2rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            padding: 15px;
        }
        
        .brand-logo {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }
        
        .brand-text {
            color: #ffffff;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 20px;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .navbar-default .navbar-brand:hover {
            color: #f8f9fa;
        }
        
        .nav-link {
            color: rgb(0, 103, 176) !important;
        }
        
        .nav-link:hover {
            color: rgb(0, 66, 113) !important;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('../../public/assets/bg-library.webp');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
        }
        
        .user-profile {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-left: 20px;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .search-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
        }

        .dashboard-header {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('../../assets/bg-library.jpg');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        #wrapper {
            background-color: #2c3e50;
        }

        .sidebar {
            background-color: #2c3e50;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
            color: #ffffff;
        }

        .sidebar ul li.active a {
            background-color: #3498db;
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="../../public/assets/logo-rumahbaca.png" alt="Logo" class="brand-logo">
                <span class="brand-text"></span>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="data_buku.php">Data Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="data_peminjaman.php">Data Peminjaman</a>
                </li>
                <li>
                    <a class="nav-item">
                        <a class="nav-link" href="riwayat_baca.php">Riwayat Baca</a>
                    </a>
                </li>
            </ul>
            
            <div class="user-profile d-flex align-items-center">
                <?php
                $sql_anggota = "SELECT a.* FROM t_anggota a 
                               JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
                               WHERE acc.username = ?";
                $stmt = mysqli_prepare($db, $sql_anggota);
                mysqli_stmt_bind_param($stmt, "s", $_SESSION['login_user']);
                mysqli_stmt_execute($stmt);
                $result_anggota = mysqli_stmt_get_result($stmt);
                $anggota = mysqli_fetch_assoc($result_anggota);
                ?>
                <div class="dropdown">
                    <a class="text-dark dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle fa-2x mr-2"></i>
                        <?php echo htmlspecialchars($anggota['nama']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="profil.php">
                            <i class="fas fa-user mr-2"></i>Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery first, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 