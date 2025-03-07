<?php
include("header.php");
require_once '../setting/koneksi.php';

if(!isset($_GET['id'])) {
    echo "<script>alert('ID Peminjaman tidak ditemukan!'); window.location='data_peminjaman.php';</script>";
    exit;
}

$id_peminjaman = mysqli_real_escape_string($db, $_GET['id']);

// Ambil data peminjaman
$sql = "SELECT p.*, s.nama as nama_staff, a.no_anggota, a.nama as nama_anggota 
        FROM t_peminjaman p 
        LEFT JOIN t_staff s ON p.id_t_staff = s.id_t_staff
        LEFT JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota
        WHERE p.id_t_peminjaman = '$id_peminjaman'";
$result = mysqli_query($db, $sql);
$peminjaman = mysqli_fetch_assoc($result);

if(!$peminjaman) {
    echo "<script>alert('Data Peminjaman tidak ditemukan!'); window.location='data_peminjaman.php';</script>";
    exit;
}
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Detail Peminjaman</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Informasi Peminjaman #<?php echo $peminjaman['no_peminjaman']; ?></h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-striped">
                                <tr>
                                    <th width="35%">No. Peminjaman</th>
                                    <td width="65%"><?php echo $peminjaman['no_peminjaman']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td><?php echo date('d/m/Y', strtotime($peminjaman['tgl_pinjam'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Kembali</th>
                                    <td><?php echo $peminjaman['tgl_kembali'] ? date('d/m/Y', strtotime($peminjaman['tgl_kembali'])) : '<span class="text-warning">Belum dikembalikan</span>'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped">
                                <tr>
                                    <th width="35%">No. Anggota</th>
                                    <td width="65%"><?php echo $peminjaman['no_anggota']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Anggota</th>
                                    <td><?php echo $peminjaman['nama_anggota']; ?></td>
                                </tr>
                                <tr>
                                    <th>Staff</th>
                                    <td><?php 
                                        if ($peminjaman['id_t_staff'] === null) {
                                            echo "-";
                                        } else {
                                            echo "Staff-" . $peminjaman['id_t_staff'];
                                        }
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Detail Buku yang Dipinjam</h4>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="30%">Nama Buku</th>
                                                <th width="20%">Penulis</th>
                                                <th width="10%">Qty</th>
                                                <th width="15%">Kondisi</th>
                                                <th width="20%">Denda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_detail = "SELECT d.*, b.nama_buku, b.penulis 
                                                         FROM t_detil_pinjam d 
                                                         JOIN t_buku b ON d.id_t_buku = b.id_t_buku 
                                                         WHERE d.id_t_peminjaman = '$id_peminjaman'";
                                            $result_detail = mysqli_query($db, $sql_detail);
                                            $no = 1;
                                            $total_denda = 0;

                                            while($detail = mysqli_fetch_assoc($result_detail)) {
                                                $total_denda += $detail['denda'];
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td><?php echo $detail['nama_buku']; ?></td>
                                                    <td><?php echo $detail['penulis']; ?></td>
                                                    <td class="text-center"><?php echo $detail['qty']; ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if($peminjaman['status'] == 'Dipinjam' || $peminjaman['status'] == 'Belum Kembali') {
                                                            echo "-";
                                                        } else {
                                                            $kondisi_class = '';
                                                            switch($detail['kondisi']) {
                                                                case 'Bagus':
                                                                    $kondisi_class = 'text-success';
                                                                    break;
                                                                case 'Rusak':
                                                                    $kondisi_class = 'text-warning';
                                                                    break;
                                                                case 'Hilang':
                                                                    $kondisi_class = 'text-danger';
                                                                    break;
                                                            }
                                                            echo "<span class='$kondisi_class'>".$detail['kondisi']."</span>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <?php echo $detail['denda'] ? 'Rp ' . number_format($detail['denda'],0,',','.') : '-'; ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                            <tr class="info">
                                                <td colspan="5" class="text-right"><strong>Total Denda</strong></td>
                                                <td class="text-right"><strong>Rp <?php echo number_format($total_denda,0,',','.'); ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <a href="data_peminjaman.php" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                            <?php if(!$peminjaman['tgl_kembali']): ?>
                            <a href="edit_detil_pinjam.php?id=<?php echo $id_peminjaman; ?>" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Peminjaman
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mt-4 {
    margin-top: 20px;
}
.panel-title {
    margin-top: 0;
    margin-bottom: 0;
    font-size: 16px;
}
.text-warning {
    color: #f0ad4e;
}
.text-success {
    color: #5cb85c;
}
.text-danger {
    color: #d9534f;
}
</style>

<?php 
// include "footer.php"; 
?>