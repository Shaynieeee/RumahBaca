<?php
include("header.php");
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

$id_peminjaman = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil data peminjaman
$sql_pinjam = "SELECT p.*, a.nama as nama_anggota 
               FROM t_peminjaman p 
               LEFT JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
               WHERE p.id_t_peminjaman = ?";
$stmt = mysqli_prepare($db, $sql_pinjam);
mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
mysqli_stmt_execute($stmt);
$result_pinjam = mysqli_stmt_get_result($stmt);
$data_pinjam = mysqli_fetch_assoc($result_pinjam);

// Ambil detail buku yang dipinjam
$sql_detail = "SELECT dp.*, b.nama_buku, b.penulis, b.harga 
               FROM t_detil_pinjam dp 
               JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
               WHERE dp.id_t_peminjaman = ?";
$stmt = mysqli_prepare($db, $sql_detail);
mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
mysqli_stmt_execute($stmt);
$result_detail = mysqli_stmt_get_result($stmt);

if(!$data_pinjam) {
    echo "<script>alert('Data Peminjaman tidak ditemukan!'); window.location='data_peminjaman.php';</script>";
    exit;
}

// Proses update jika ada POST
if(isset($_POST['submit'])) {
    $id_detil = $_POST['id_detil'];
    $id_peminjaman = $_POST['id_peminjaman'];
    $qty = $_POST['qty'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'];
    $denda = $_POST['denda'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $status = $_POST['status']; // Ambil status dari form

    mysqli_begin_transaction($db);
    
    try {
        // Update detail peminjaman
        $sql_update_detail = "UPDATE t_detil_pinjam SET 
                            qty = '$qty',
                            kondisi = '$kondisi',
                            keterangan = '$keterangan',
                            denda = '$denda'
                            WHERE id_t_detil_pinjam = '$id_detil'";
        mysqli_query($db, $sql_update_detail);

        // Update tanggal kembali dan status di tabel peminjaman
        $sql_update_pinjam = "UPDATE t_peminjaman SET 
                             tgl_kembali = " . ($tgl_kembali ? "'$tgl_kembali'" : "NULL") . ",
                             status = '$status'
                             WHERE id_t_peminjaman = '$id_peminjaman'";
        mysqli_query($db, $sql_update_pinjam);

        mysqli_commit($db);
        echo "<script>alert('Data berhasil diupdate!'); window.location='data_peminjaman.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo "<script>alert('Gagal mengupdate data: " . $e->getMessage() . "');</script>";
    }
}
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Edit Peminjaman</h1>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Detail Peminjaman</h4>
        </div>
        <div class="panel-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>No Peminjaman</th>
                            <td><?php echo $data_pinjam['no_peminjaman']; ?></td>
                        </tr>
                        <tr>
                            <th>Nama Anggota</th>
                            <td><?php echo $data_pinjam['nama_anggota']; ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pinjam</th>
                            <td><?php echo date('d/m/Y', strtotime($data_pinjam['tgl_pinjam'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td><?php echo $data_pinjam['status']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <form method="POST" action="proses_edit_peminjaman.php">
                <input type="hidden" name="id_peminjaman" value="<?php echo $id_peminjaman; ?>">
                
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result_detail)): 
                ?>
                <div class="buku-item panel panel-default">
                    <div class="panel-heading">
                        <h4>Buku <?php echo $no++; ?></h4>
                    </div>
                    <div class="panel-body">
                        <input type="hidden" name="id_detil[]" value="<?php echo $row['id_t_detil_pinjam']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul Buku</label>
                                    <input type="text" class="form-control" value="<?php echo $row['nama_buku']; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Penulis</label>
                                    <input type="text" class="form-control" value="<?php echo $row['penulis']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kondisi Saat Kembali</label>
                                    <select name="kondisi[]" class="form-control kondisi-select" required 
                                            data-harga="<?php echo $row['harga']; ?>" 
                                            data-denda-input="#denda_<?php echo $row['id_t_detil_pinjam']; ?>">
                                        <option value="">Pilih Kondisi</option>
                                        <option value="Baik" <?php echo ($row['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                                        <option value="Rusak" <?php echo ($row['kondisi'] == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                        <option value="Hilang" <?php echo ($row['kondisi'] == 'Hilang') ? 'selected' : ''; ?>>Hilang</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Denda</label>
                                    <input type="number" name="denda[]" id="denda_<?php echo $row['id_t_detil_pinjam']; ?>" 
                                           class="form-control" value="<?php echo $row['denda']; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan[]" class="form-control" rows="2"><?php echo $row['keterangan']; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <div class="form-group">
                    <label>Status Peminjaman</label>
                    <select name="status" class="form-control" required>
                        <option value="Dipinjam" <?php echo ($data_pinjam['status'] == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="Sudah Kembali" <?php echo ($data_pinjam['status'] == 'Sudah Kembali') ? 'selected' : ''; ?>>Sudah Kembali</option>
                        <option value="Belum Kembali" <?php echo ($data_pinjam['status'] == 'Belum Kembali') ? 'selected' : ''; ?>>Belum Kembali</option>
                    </select>
                </div>

                <div class="form-group">
                    <a href="data_peminjaman.php" class="btn btn-default">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.buku-item {
    margin-bottom: 20px;
}
.panel-heading h4 {
    margin: 0;
}
.form-group {
    margin-bottom: 15px;
}
</style>

<script>
$(document).ready(function() {
    // Fungsi untuk menghitung denda
    function hitungDenda(kondisi, harga) {
        switch(kondisi) {
            case 'Rusak':
                return Math.round(harga * 0.3); // 30% dari harga buku
            case 'Hilang':
                return harga; // 100% harga buku
            default:
                return 0; // Tidak ada denda untuk kondisi Baik
        }
    }

    // Event handler untuk perubahan kondisi
    $('.kondisi-select').change(function() {
        var kondisi = $(this).val();
        var harga = parseInt($(this).data('harga'));
        var dendaInput = $(this).data('denda-input');
        
        var denda = hitungDenda(kondisi, harga);
        $(dendaInput).val(denda);
    });

    // Hitung denda awal saat halaman dimuat
    $('.kondisi-select').each(function() {
        $(this).trigger('change');
    });

    // Event handler untuk perubahan status peminjaman
    $('select[name="status"]').change(function() {
        var status = $(this).val();
        var isActive = (status == 'Dipinjam' || status == 'Belum Kembali');
        
        // Update tampilan form berdasarkan status
        $('.kondisi-select').each(function() {
            var container = $(this).closest('.col-md-6');
            if(isActive) {
                container.find('input[type="text"]').val('-');
                container.find('textarea').val('-').prop('readonly', true);
                container.find('input[name="denda[]"]').val('0');
            } else {
                container.find('textarea').prop('readonly', false);
                $(this).trigger('change'); // Recalculate denda
            }
        });
    });
});
</script>

<?php 
// include "footer.php"; 
?>