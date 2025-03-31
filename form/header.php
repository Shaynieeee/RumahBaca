<?php 
if(!isset($_SESSION)) { 
    session_start(); 
}

// Cek login
if(!isset($_SESSION['login_user'])){
    header("location:../index.php");
    exit;
}

require_once __DIR__ . '/../setting/koneksi.php';

$usersession = $_SESSION['login_user'];
$sql = "SELECT id_p_role FROM t_account WHERE username = '$usersession'";
$result = mysqli_query($db, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

// Simpan role di variabel
$role = $row['id_p_role'];

// Cek keterlambatan pengembalian untuk admin/staff
if($role == 1 || $role == 2) {
    $sql_keterlambatan = "SELECT p.id_t_peminjaman, a.nama, a.id_t_anggota, p.tgl_kembali, 
                         GROUP_CONCAT(b.nama_buku SEPARATOR ', ') as daftar_buku,
                         DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat
                         FROM t_peminjaman p 
                         JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
                         JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                         JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
                         WHERE p.status = 'Dipinjam' 
                         AND CURDATE() > p.tgl_kembali
                         GROUP BY p.id_t_peminjaman";
    
    $result_keterlambatan = mysqli_query($db, $sql_keterlambatan);
    $jumlah_keterlambatan = mysqli_num_rows($result_keterlambatan);
}

// Jangan redirect di sini, biarkan tampilan menyesuaikan role
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rumah Baca</title>

    <!-- Bootstrap Core CSS -->
    <link href="../template/css/bootstrap.min.css" rel="stylesheet">
    <!-- MetisMenu CSS -->
    <link href="../template/css/metisMenu.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="../template/css/dataTables.bootstrap.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="../template/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- jQuery -->
    <script src="../template/js/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="../template/js/bootstrap.min.js"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="../template/js/metisMenu.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="../template/js/jquery.dataTables.min.js"></script>
    <script src="../template/js/dataTables.bootstrap.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../template/js/sb-admin-2.js"></script>

    <!-- Tambahkan Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Tambahkan Icons8 Line Awesome -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">

    <style>
        /* Perbaikan style untuk sidebar */
        .sidebar {
            width: 230px !important; /* Mengecilkan lebar sidebar */
            position: fixed;
            z-index: 1;
            top: 41px;
            bottom: 0;
            left: 0;
            overflow-x: hidden;
            overflow-y: auto;
            background-color: #f5f5f5;
            border-right: 1px solid #eee;
        }

        /* Menyesuaikan konten utama */
        #page-wrapper {
            margin-left: 200px !important; /* Sesuaikan dengan lebar sidebar */
            padding: 20px;
        }

        /* Style untuk menu */
        .nav-sidebar {
            padding: 0;
        }

        .nav-sidebar > li > a {
            padding: 10px 15px;
            color: #333;
        }

        /* Style untuk submenu */
        .children {
            padding-left: 25px;
            list-style: none;
        }

        .children > li > a {
            padding: 5px 15px;
            display: block;
            color: #666;
            text-decoration: none;
        }

        /* Style untuk icon menu */
        .nav-sidebar .glyphicon {
            margin-right: 10px;
        }

        /* Style untuk arrow dropdown */
        .icon.pull-right {
            margin-top: 3px;
        }

        /* Hover effect */
        .nav-sidebar > li > a:hover,
        .children > li > a:hover {
            background-color: #eee;
        }

        /* Active state */
        .nav-sidebar > .active > a,
        .nav-sidebar > .active > a:hover {
            color: #fff;
            background-color: #428bca;
        }

        /* Responsive adjustment */
        @media (max-width: 768px) {
            .sidebar {
                width: 100% !important;
                position: relative;
                top: 0;
                margin-bottom: 20px;
            }
            
            #page-wrapper {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="">
            <!-- Sidebar -->
            <div class="col-sm-5 col-md-3 sidebar" style="margin-top: 10px;">
                <ul class="nav nav-sidebar">
                    <li><a href="dashboard.php"><span class="glyphicon glyphicon-home"></span>&nbsp;Dashboard</a></li>
                    
                    <!-- Menu Peminjaman -->
                    <li class="parent">
                        <a href="#">
                            <span class="glyphicon glyphicon-list" data-toggle="collapse" href="#sub-item-1" class="icon pull-right">&nbsp;Peminjaman</span><span data-toggle="collapse" href="#sub-item-1" class="icon pull-right"><em class="glyphicon glyphicon-arrow-down"></em></span> 
                        </a>
                        <ul class="children collapse" id="sub-item-1">
                            <li><a href="data_peminjaman.php">Data Peminjaman</a></li>
                            <li><a href="input_peminjaman.php">Input Data Peminjaman</a></li>
                            <!-- <li><a href="laporan_peminjaman.php">Laporan Peminjaman</a></li> -->
                        </ul>
                    </li>
                    
                    <!-- Menu Buku -->
                    <li class="parent">
                        <a href="#">
                            <span class="glyphicon glyphicon-book" data-toggle="collapse" href="#sub-item-2">&nbsp;Buku</span><span data-toggle="collapse" href="#sub-item-2" class="icon pull-right"><em class="glyphicon glyphicon-arrow-down"></em></span> 
                        </a>
                        <ul class="children collapse" id="sub-item-2">
                            <li><a href="data_buku.php">Data Buku</a></li>
                            <li><a href="input_buku.php">Input Data Buku</a></li>
                            <!-- <li><a href="laporan_buku.php">Laporan Buku</a></li> -->
                        </ul>
                    </li>
                    
                    <!-- Menu Anggota -->
                    <li class="parent">
                        <a href="#">
                            <span class="glyphicon glyphicon-user" data-toggle="collapse" href="#sub-item-3">&nbsp;Anggota </span>
                            <span data-toggle="collapse" href="#sub-item-3" class="icon pull-right">
                                <em class="glyphicon glyphicon-arrow-down"></em>
                            </span> 
                        </a>
                        <ul class="children collapse" id="sub-item-3">
                            <li><a href="data_anggota.php">Data Anggota</a></li>
                            <li><a href="input_anggota.php">Input Anggota</a></li>
                            <!-- <li><a href="laporan_anggota.php">Laporan Anggota</a></li> -->
                        </ul>
                    </li>
                    
                    <?php if($role == 1): // Menu Staff hanya untuk Admin (role = 1) ?>
                    <!-- Menu Staff -->
                    <li class="parent">
                        <a href="#">
                            <span class="glyphicon glyphicon-user" data-toggle="collapse" href="#sub-item-4">&nbsp;Staff </span><span data-toggle="collapse" href="#sub-item-4" class="icon pull-right"><em class="glyphicon glyphicon-arrow-down"></em></span> 
                        </a>
                        <ul class="children collapse" id="sub-item-4">
                            <li><a href="data_staff.php">Data Staff</a></li>
                            <li><a href="input_staff.php">Input Data Staff</a></li>
                            <!-- <li><a href="laporan_staff.php">Laporan Staff</a></li> -->
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Menu Rating -->
                    <!-- <li><a href="data_rating.php"><span class="glyphicon glyphicon-star"></span>&nbsp;Rating</a></li> -->
                    <li><a href="pengaturan.php"><span class="glyphicon glyphicon-cog"></span>&nbsp;Pengaturan</a></li>


                    <!-- Menu Laporan -->
                    <!-- <li>
                        <a href="#">
                            <span class="glyphicon glyphicon-stats"></span>&nbsp;Laporan <span data-toggle="collapse" href="#sub-item-5" class="icon pull-right"><em class="glyphicon glyphicon-arrow-down"></em></span> 
                        </a>
                        <ul class="children collapse" id="sub-item-5">
                            <li><a href="laporan_buku.php">Laporan Buku</a></li>
                            <li><a href="laporan_peminjaman.php">Laporan Peminjaman</a></li>
                            <li><a href="laporan_anggota.php">Laporan Anggota</a></li>
                            <?php if($role == 1): // Laporan Staff hanya untuk Admin ?>
                            <li><a href="laporan_staff.php">Laporan Staff</a></li>
                            <?php endif; ?>
                        </ul>
                    </li> -->

            <!-- Content -->
            <div class="">
                <div class="">
                <!-- Konten utama akan dimuat di sini -->
                </div>
            </div>
        </div>
    </div>

<!-- Navigation -->
<nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
    <div class="container-fluid">
        <div class="navbar-header">
            <!-- Logo Pelindo di sebelah kiri -->
            <a class="navbar-brand" href="index.php" style="padding: 10px 15px;">
                <img src="../public/assets/logo pelindo.png" alt="Logo Pelindo" height="30" style="display: inline-block; vertical-align: middle;">
                <span style="margin-left: 10px;"></span>
            </a>
            

            
            <!-- Tombol toggle untuk mobile -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="glyphicon glyphicon-user"></span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li>
                            <a href="profile.php">
                                <span class="glyphicon glyphicon-tasks"></span>&nbsp;Profile
                            </a>
                        </li>
                        <li>
                            <a href="ganti_password.php">
                                <span class="glyphicon glyphicon-lock"></span>&nbsp;Ganti Password
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="../logout.php">
                                <span class="glyphicon glyphicon-off"></span>&nbsp;Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal Keterlambatan -->
<?php if(($role == 1 || $role == 2) && $jumlah_keterlambatan > 0): ?>
<div class="modal fade" id="modalKeterlambatan" tabindex="-1" role="dialog" aria-labelledby="modalKeterlambatanLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalKeterlambatanLabel">Daftar Keterlambatan Pengembalian</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Buku</th>
                                <th>Tanggal Kembali</th>
                                <th>Keterlambatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row_keterlambatan = mysqli_fetch_assoc($result_keterlambatan)): ?>
                            <tr>
                                <td><?php echo $row_keterlambatan['nama']; ?></td>
                                <td><?php echo $row_keterlambatan['daftar_buku']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row_keterlambatan['tgl_kembali'])); ?></td>
                                <td><?php echo $row_keterlambatan['hari_terlambat']; ?> hari</td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="kirimPengingat(<?php echo $row_keterlambatan['id_t_peminjaman']; ?>, <?php echo $row_keterlambatan['id_t_anggota']; ?>)">
                                        <i class="fa fa-envelope"></i> Kirim Pengingat
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script untuk mengirim pengingat -->
<script>
function kirimPengingat(idPeminjaman, idAnggota) {
    if(confirm('Apakah Anda yakin ingin mengirim pengingat dan menonaktifkan akun anggota ini?')) {
        $.ajax({
            url: 'proses_kirim_pengingat.php',
            type: 'POST',
            data: {
                id_peminjaman: idPeminjaman,
                id_anggota: idAnggota
            },
            success: function(response) {
                alert(response);
            },
            error: function() {
                alert('Terjadi kesalahan saat mengirim pengingat.');
            }
        });
    }
}
</script>

<!-- Pastikan jQuery dan Bootstrap JS dimuat dengan benar -->
<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/vendor/metisMenu/metisMenu.min.js"></script>
<script src="../assets/dist/js/sb-admin-2.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi dropdown
    $('.dropdown-toggle').dropdown();
    
    // Tambahkan event handler untuk item dropdown
    $('.dropdown-menu li a').click(function(e) {
        window.location = $(this).attr('href');
    });

    // Pastikan dropdown tetap terlihat saat hover
    $('.dropdown').hover(
        function() {
            $(this).addClass('open');
        },
        function() {
            $(this).removeClass('open');
        }
    );
});
</script>
</body>
</html>