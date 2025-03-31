<?php
session_start();
require_once '../../setting/koneksi.php';

// Cek apakah user sudah login
if(!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

include("header_anggota.php");

// Ambil username anggota yang login
$username = $_SESSION['login_user'];

// Ambil id_anggota dari user yang login
$get_anggota = "SELECT a.id_t_anggota 
                FROM t_anggota a 
                JOIN t_account ac ON a.id_t_account = ac.id_t_account 
                WHERE ac.username = ?";
$stmt_anggota = mysqli_prepare($db, $get_anggota);
mysqli_stmt_bind_param($stmt_anggota, "s", $username);
mysqli_stmt_execute($stmt_anggota);
$result_anggota = mysqli_stmt_get_result($stmt_anggota);
$anggota = mysqli_fetch_assoc($result_anggota);
$id_anggota = $anggota['id_t_anggota'];

// Ambil data peminjaman aktif (belum dikembalikan)
$sql = "SELECT p.*, b.nama_buku, b.penulis, dp.qty, dp.kondisi, dp.denda 
        FROM t_peminjaman p
        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
        WHERE p.id_t_anggota = ? 
        AND (p.status = 'Dipinjam' OR p.status = 'Belum Kembali')
        ORDER BY p.tgl_pinjam DESC";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_anggota);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ambil riwayat peminjaman (sudah dikembalikan)
$sql_history = "SELECT p.*, dp.*, b.nama_buku, 
                DATE_ADD(p.tgl_pinjam, INTERVAL 7 DAY) as batas_kembali,
                p.tgl_kembali as tanggal_kembali
                FROM t_peminjaman p 
                JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
                WHERE p.id_t_anggota = ? 
                AND p.status = 'Sudah Kembali'
                ORDER BY p.tgl_kembali DESC";
$stmt_history = mysqli_prepare($db, $sql_history);
mysqli_stmt_bind_param($stmt_history, "i", $id_anggota);
mysqli_stmt_execute($stmt_history);
$result_history = mysqli_stmt_get_result($stmt_history);
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Data Peminjaman</h1>
        </div>
    </div>
    
    <!-- Peminjaman Aktif -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Peminjaman Aktif
                </div>
                <div class="panel-body">
                    <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-active">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Peminjaman</th>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$no."</td>";
                                echo "<td>".$row['no_peminjaman']."</td>";
                                echo "<td>".$row['nama_buku']."</td>";
                                echo "<td>".$row['penulis']."</td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tgl_pinjam']))."</td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tgl_kembali']))."</td>";
                                echo "<td>".$row['qty']."</td>";
                                echo "<td><span class='label label-warning'>".$row['status']."</span></td>";
                                echo "</tr>";
                                $no++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- History Peminjaman -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Riwayat Peminjaman
                </div>
                <div class="panel-body">
                    <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-history">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Peminjaman</th>
                                <th>Judul Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Qty</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while($row = mysqli_fetch_array($result_history, MYSQLI_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".$no."</td>";
                                echo "<td>".$row['no_peminjaman']."</td>";
                                echo "<td>".$row['nama_buku']."</td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tgl_pinjam']))."</td>";
                                echo "<td>".date('d-m-Y', strtotime($row['tanggal_kembali']))."</td>";
                                echo "<td>".$row['qty']."</td>";
                                echo "<td>".$row['kondisi']."</td>";
                                echo "<td><span class='label label-success'>".$row['status']."</span></td>";
                                echo "<td>Rp. ".number_format($row['denda'],0,',','.')."</td>";
                                echo "</tr>";
                                $no++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?> 