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

// Ambil pengaturan denda
$sql_denda = "SELECT * FROM t_pengaturan_denda";
$result_denda = mysqli_query($db, $sql_denda);
$pengaturan_denda = [];
while($row = mysqli_fetch_assoc($result_denda)) {
    $pengaturan_denda[$row['jenis_denda']] = $row['nilai_denda'];
}

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
    $denda = $_POST['denda']; // Denda dari kondisi buku
    $tgl_kembali = $_POST['tgl_kembali'];
    $status = $_POST['status'];
    
    mysqli_begin_transaction($db);
    
    try {
        // Ambil data peminjaman untuk hitung keterlambatan
        $sql_pinjam = "SELECT p.*, b.harga, DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat 
                       FROM t_peminjaman p 
                       JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                       JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
                       WHERE p.id_t_peminjaman = ?";
        $stmt = mysqli_prepare($db, $sql_pinjam);
        mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data_pinjam = mysqli_fetch_assoc($result);
        
        // Hitung denda keterlambatan
            $denda_terlambat = 0;
        if ($data_pinjam['hari_terlambat'] > 0) {
            $denda_terlambat = $data_pinjam['hari_terlambat'] * 2000; // Rp 2.000 per hari
        }
        
        // Total denda = denda keterlambatan + denda kondisi buku
        $total_denda = $denda_terlambat + $denda;
            
            // Update detail peminjaman
        $sql_update = "UPDATE t_detil_pinjam SET 
                                qty = ?,
                                kondisi = ?,
                                keterangan = ?,
                       denda = ?,
                       update_date = CURDATE(),
                       update_by = ?
                                WHERE id_t_detil_pinjam = ?";
        $stmt = mysqli_prepare($db, $sql_update);
        $update_by = substr($_SESSION['login_user'] ?? 'SYS', 0, 3);
        mysqli_stmt_bind_param($stmt, "issisi", $qty, $kondisi, $keterangan, $total_denda, $update_by, $id_detil);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating detail: " . mysqli_error($db));
        }
        
        // Update status peminjaman
        $sql_status = "UPDATE t_peminjaman SET 
                       status = ?,
                       tgl_kembali = ?,
                       update_date = CURDATE(),
                       update_by = ?
                       WHERE id_t_peminjaman = ?";
        $stmt = mysqli_prepare($db, $sql_status);
        mysqli_stmt_bind_param($stmt, "sssi", $status, $tgl_kembali, $update_by, $id_peminjaman);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating status: " . mysqli_error($db));
        }
        
        // Jika status "Dikembalikan", kembalikan stok buku
        if ($status == 'Dikembalikan') {
            $sql_stok = "UPDATE t_buku b 
                        JOIN t_detil_pinjam dp ON b.id_t_buku = dp.id_t_buku 
                        SET b.stok = b.stok + dp.qty 
                        WHERE dp.id_t_detil_pinjam = ?";
            $stmt = mysqli_prepare($db, $sql_stok);
            mysqli_stmt_bind_param($stmt, "i", $id_detil);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating stock: " . mysqli_error($db));
            }
        }

        mysqli_commit($db);
        echo "<script>
                alert('Data berhasil diupdate!');
                window.location='data_peminjaman.php';
              </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
              </script>";
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
            <?php
            // Tampilkan informasi keterlambatan jika ada
            if($data_pinjam['status'] == 'Belum Kembali') {
                $days_late = floor((strtotime('now') - strtotime($data_pinjam['tgl_pinjam'])) / (60 * 60 * 24));
                if($days_late > 0) {
                    echo '<div class="alert alert-warning">';
                    echo 'Buku terlambat dikembalikan ' . $days_late . ' hari. ';
                    echo 'Denda keterlambatan akan dihitung 10% dari harga buku per hari.';
                    echo '</div>';
                }
            }
            ?>
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
        var dendaRusak = <?php echo isset($pengaturan_denda['rusak']) ? $pengaturan_denda['rusak'] : 30; ?>;
        var dendaHilang = <?php echo isset($pengaturan_denda['hilang']) ? $pengaturan_denda['hilang'] : 100; ?>;
        
        switch(kondisi) {
            case 'Rusak':
                return Math.round(harga * (dendaRusak/100));
            case 'Hilang':
                return Math.round(harga * (dendaHilang/100));
            default:
                return 0;
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