<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/error.log');
error_log("Pesan error atau debug", 3, __DIR__ . "/error.log");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include("header.php");
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

$id_peminjaman = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil data peminjaman
// Query ambil data peminjaman
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

// Ambil pengaturan denda dari database
$sql_denda = "SELECT * FROM t_pengaturan_denda";
$result_denda = mysqli_query($db, $sql_denda);
$pengaturan_denda = [];
while ($row = mysqli_fetch_assoc($result_denda)) {
    $pengaturan_denda[$row['jenis_denda']] = $row['nilai_denda'];
}

if (!$data_pinjam) {
    echo "<script>alert('Data Peminjaman tidak ditemukan!'); window.location='data_peminjaman.php';</script>";
    exit;
}

// Proses update jika ada POST
if (isset($_POST['submit'])) {
    $id_detil = $_POST['id_detil'];
    $id_peminjaman = $_POST['id_peminjaman'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'];
    $denda = $_POST['denda'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $status = $_POST['status'];

    // Debug log
    error_log("POST data: " . print_r($_POST, true), 3, __DIR__ . "/error.log");
    error_log("Processing peminjaman ID: $id_peminjaman", 3, __DIR__ . "/error.log");

    mysqli_begin_transaction($db);
    $error_occurred = false;
    $error_message = '';

    // Hitung denda keterlambatan
    $sql_pinjam = "SELECT tgl_kembali FROM t_peminjaman WHERE id_t_peminjaman = ?";
    $stmt = mysqli_prepare($db, $sql_pinjam);
    mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data_pinjam = mysqli_fetch_assoc($result);
    $tgl_kembali_db = $data_pinjam['tgl_kembali'];
    $hari_terlambat = (strtotime(date('Y-m-d')) > strtotime($tgl_kembali_db)) ? floor((strtotime(date('Y-m-d')) - strtotime($tgl_kembali_db)) / (60 * 60 * 24)) : 0;
    $nilai_denda_terlambat = isset($pengaturan_denda['terlambat']) ? $pengaturan_denda['terlambat'] : 5000;
    $nilai_denda_rusak = isset($pengaturan_denda['rusak']) ? $pengaturan_denda['rusak'] : 30;
    $nilai_denda_hilang = isset($pengaturan_denda['hilang']) ? $pengaturan_denda['hilang'] : 100;

    foreach ($id_detil as $idx => $id) {
        $current_kondisi = isset($kondisi[$id][0]) ? $kondisi[$id][0] : '';
        $current_keterangan = isset($keterangan[$id][0]) ? $keterangan[$id][0] : '';

        // Ambil harga buku
        $sql_book = "SELECT b.harga FROM t_detil_pinjam dp JOIN t_buku b ON dp.id_t_buku = b.id_t_buku WHERE dp.id_t_detil_pinjam = ?";
        $stmt = mysqli_prepare($db, $sql_book);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $book_result = mysqli_stmt_get_result($stmt);
        $book_data = mysqli_fetch_assoc($book_result);
        $harga_buku = $book_data['harga'];

        // Hitung denda
        $denda_kondisi = 0;
        if ($current_kondisi == 'Rusak') {
            $denda_kondisi = round($harga_buku * ($nilai_denda_rusak / 100));
        } elseif ($current_kondisi == 'Hilang') {
            $denda_kondisi = round($harga_buku * ($nilai_denda_hilang / 100));
        } else {
            $denda_kondisi = 0; // Kondisi Baik tidak ada denda
        }
        $denda_terlambat = ($hari_terlambat > 0) ? $hari_terlambat * $nilai_denda_terlambat : 0;
        $total_denda = $denda_kondisi + $denda_terlambat;

        $sql_update = "UPDATE t_detil_pinjam SET kondisi = ?, keterangan = ?, denda = ?, update_date = CURDATE(), update_by = ? WHERE id_t_detil_pinjam = ?";
        $stmt = mysqli_prepare($db, $sql_update);
        $update_by = substr($_SESSION['login_user'] ?? 'SYS', 0, 3);
        mysqli_stmt_bind_param($stmt, "ssisi", $current_kondisi, $current_keterangan, $total_denda, $update_by, $id);
        if (!mysqli_stmt_execute($stmt)) {
            $error_occurred = true;
            $error_message = "Error updating detail: " . mysqli_error($db);
            break;
        }
    }

    if (!$error_occurred) {
        // Calculate and update total denda for the entire peminjaman
        $sql_total_denda = "SELECT SUM(denda) as total_denda FROM t_detil_pinjam WHERE id_t_peminjaman = ?";
        $stmt = mysqli_prepare($db, $sql_total_denda);
        mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $final_total_denda = $row['total_denda'];

        $sql_denda = "UPDATE t_peminjaman SET total_denda = ? WHERE id_t_peminjaman = ?";
        $stmt = mysqli_prepare($db, $sql_denda);
        mysqli_stmt_bind_param($stmt, "ii", $final_total_denda, $id_peminjaman);
        if (!mysqli_stmt_execute($stmt)) {
            $error_occurred = true;
            $error_message = "Error updating total denda: " . mysqli_error($db);
        }
    }

    if (!$error_occurred) {
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
            $error_occurred = true;
            $error_message = "Error updating status: " . mysqli_error($db);
        }
    }

    if (!$error_occurred && $status == 'Sudah Kembali') {
        // Update stok buku untuk setiap detail yang dikembalikan
        foreach ($id_detil as $id) {
            $sql_stok = "UPDATE t_buku b JOIN t_detil_pinjam dp ON b.id_t_buku = dp.id_t_buku SET b.stok = b.stok + 1 WHERE dp.id_t_detil_pinjam = ?";
            $stmt = mysqli_prepare($db, $sql_stok);
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (!mysqli_stmt_execute($stmt)) {
                $error_occurred = true;
                $error_message = "Error updating stock: " . mysqli_error($db);
                break;
            }
        }
    }

    if (!$error_occurred) {
        mysqli_commit($db);
        echo "<div class='alert alert-success'>Data berhasil diupdate!</div>";
        echo "<script>window.location='data_peminjaman.php';</script>";
        exit;
    } else {
        mysqli_rollback($db);
        echo "<div class='alert alert-danger'>Error: $error_message</div>";
        echo "<script>alert('Error: $error_message');</script>";
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
            if ($data_pinjam['status'] == 'Belum Kembali') {
                $days_late = floor((strtotime('now') - strtotime($data_pinjam['tgl_pinjam'])) / (60 * 60 * 24));
                if ($days_late > 0) {
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
                            <th>Tanggal Harus Kembali</th>
                            <td><?php echo date('d/m/Y', strtotime($data_pinjam['tgl_kembali'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status Saat Ini</th>
                            <td><?php echo $data_pinjam['status']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="id_peminjaman" value="<?php echo $id_peminjaman; ?>">
                <input type="hidden" name="tgl_kembali" value="<?php echo date('Y-m-d'); ?>">

                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($result_detail)):
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
                                        <input type="text" class="form-control" value="<?php echo $row['nama_buku']; ?>"
                                            readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Penulis</label>
                                        <input type="text" class="form-control" value="<?php echo $row['penulis']; ?>"
                                            readonly>
                                    </div>
                                    <!-- Qty sudah tidak digunakan karena 1 baris = 1 buku -->
                                </div>
                                <div class="col-md-6">
                                    <div class="copy-item mb-4">
                                        <div class="form-group">
                                            <label>Kondisi Saat Kembali</label>
                                            <select name="kondisi[<?php echo $row['id_t_detil_pinjam']; ?>][]"
                                                class="form-control kondisi-select" required
                                                data-harga="<?php echo $row['harga']; ?>"
                                                data-denda-input="#denda_<?php echo $row['id_t_detil_pinjam']; ?>">
                                                <option value="">Pilih Kondisi</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Rusak">Rusak</option>
                                                <option value="Hilang">Hilang</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Denda</label>
                                            <input type="number" name="denda[<?php echo $row['id_t_detil_pinjam']; ?>][]"
                                                id="denda_<?php echo $row['id_t_detil_pinjam']; ?>" class="form-control"
                                                value="0" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea name="keterangan[<?php echo $row['id_t_detil_pinjam']; ?>][]"
                                                class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <div class="form-group">
                    <label>Status Peminjaman</label>
                    <select name="status" class="form-control" required>
                        <option value="Dipinjam" <?php echo ($data_pinjam['status'] == 'Dipinjam') ? 'selected' : ''; ?>>
                            Dipinjam</option>
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
    $(document).ready(function () {
        function hitungDenda(kondisi, harga) {
            var dendaKondisi = 0;
            if (kondisi === 'Rusak') {
                dendaKondisi = Math.round(harga * 0.3); // 30% dari harga buku
            } else if (kondisi === 'Hilang') {
                dendaKondisi = Math.round(harga); // 100% dari harga buku
            }

            // Hitung denda keterlambatan jika ada
            var tglKembali = new Date('<?php echo $data_pinjam['tgl_kembali']; ?>');
            var today = new Date();
            var diffTime = Math.abs(today - tglKembali);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            var dendaTerlambat = 0;
            if (today > tglKembali) {
                dendaTerlambat = diffDays * <?php echo $pengaturan_denda['terlambat'] ?? 5000; ?>;
            }

            return dendaKondisi + dendaTerlambat;
        }

        $('.kondisi-select').change(function () {
            var kondisi = $(this).val();
            var harga = parseInt($(this).data('harga'));
            var dendaInput = $($(this).data('denda-input'));
            var denda = hitungDenda(kondisi, harga);
            dendaInput.val(denda);
        });

        // Hitung denda awal saat halaman dimuat
        $('.kondisi-select').each(function () {
            $(this).trigger('change');
        });
    });
</script>

<?php
// include "footer.php"; 
?>
<?php
