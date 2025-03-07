<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

$error = "";
$success = "";
$result_riwayat = null; // Inisialisasi variabel

// Mendapatkan id_staff/admin
if(isset($_SESSION['login_user'])) {
    $sql_user = "SELECT a.id_t_account, a.id_p_role, r.nama_role 
                 FROM t_account a 
                 JOIN p_role r ON a.id_p_role = r.id_p_role
                 WHERE a.username = ?";
    $stmt_user = mysqli_prepare($db, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "s", $_SESSION['login_user']);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user = mysqli_fetch_assoc($result_user);
    
    if ($user) {
        $_SESSION['id_staff'] = ($user['id_p_role'] == 2) ? $user['id_t_account'] : null; // null jika admin
        $_SESSION['role'] = $user['id_p_role'];
        $_SESSION['role_name'] = $user['nama_role'];
    }
}

// Generate no peminjaman (PJM + YYYYMMDD + 3 digit)
$today = date('Ymd');
$query = "SELECT MAX(SUBSTRING(no_peminjaman, 12)) as max_num 
         FROM t_peminjaman 
         WHERE SUBSTRING(no_peminjaman, 4, 8) = '$today'";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_assoc($result);
$next_num = str_pad((intval($row['max_num'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
$no_peminjaman = "PJM" . $today . $next_num;

if(isset($_POST['btncari'])) {
    $no_anggota = mysqli_real_escape_string($db, $_POST['no_anggota']);
    
    // Cari data anggota
    $sql = "SELECT a.*, acc.username 
            FROM t_anggota a 
            JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
            WHERE a.no_anggota = ? AND a.status = 'Aktif'";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $no_anggota);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    
    if (!$anggota) {
        $error = "No Anggota tidak ditemukan atau tidak aktif";
    } else {
        $_SESSION['selected_anggota'] = $anggota;
        
        // Perbaikan query riwayat peminjaman
        $sql_riwayat = "SELECT dp.id_t_detil_pinjam, dp.qty, 
                               b.nama_buku, b.penulis, b.harga,
                               p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam 
                        FROM t_peminjaman p
                        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
                        WHERE p.id_t_anggota = ? 
                        ORDER BY p.create_date DESC, dp.id_t_detil_pinjam DESC";
        $stmt_riwayat = mysqli_prepare($db, $sql_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $anggota['id_t_anggota']);
        mysqli_stmt_execute($stmt_riwayat);
        $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
    }
}

// Jika form tambah buku disubmit
if(isset($_POST['btntambahbuku'])) {
    // Proses penambahan buku ke detail peminjaman
    // ... kode untuk menyimpan detail buku yang dipinjam
}

// Proses simpan peminjaman
if(isset($_POST['simpan_peminjaman'])) {
    try {
        mysqli_begin_transaction($db);
        
        // Mengambil data anggota dari session
        $selected_anggota = $_SESSION['selected_anggota'];
        $id_anggota = $selected_anggota['id_t_anggota'];
        $no_anggota = $selected_anggota['no_anggota'];
        
        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = $_POST['tgl_kembali'];
        $create_by = substr($_SESSION['login_user'], 0, 3);
        
        // Set id_staff null jika admin
        $id_staff = ($_SESSION['role'] == 2) ? $_SESSION['id_staff'] : null;
        
        // 1. Insert ke t_peminjaman
        $sql_pinjam = "INSERT INTO t_peminjaman (no_peminjaman, id_t_anggota, id_t_staff, 
                       tgl_pinjam, tgl_kembali, status, create_date, create_by) 
                       VALUES (?, ?, ?, ?, ?, 'Dipinjam', CURDATE(), ?)";
        $stmt = mysqli_prepare($db, $sql_pinjam);
        mysqli_stmt_bind_param($stmt, "siisss", $no_peminjaman, $id_anggota, $id_staff,
                             $tgl_pinjam, $tgl_kembali, $create_by);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting peminjaman: " . mysqli_error($db));
        }
        
        $id_peminjaman = mysqli_insert_id($db);
        
        // 2. Insert setiap buku ke t_detil_pinjam
        if (isset($_POST['id_buku']) && is_array($_POST['id_buku'])) {
            foreach($_POST['id_buku'] as $key => $id_buku) {
                $jumlah = $_POST['jumlah_buku'][$key];
                
                $sql_detail = "INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, qty, 
                              kondisi, create_date, create_by) 
                              VALUES (?, ?, ?, 'Baik', CURDATE(), ?)";
                $stmt_detail = mysqli_prepare($db, $sql_detail);
                mysqli_stmt_bind_param($stmt_detail, "iiis", $id_peminjaman, $id_buku, $jumlah, $create_by);
                
                if (!mysqli_stmt_execute($stmt_detail)) {
                    throw new Exception("Error inserting detail: " . mysqli_error($db));
                }
                
                // Update stok buku
                $sql_update = "UPDATE t_buku SET stok = stok - ? WHERE id_t_buku = ?";
                $stmt_update = mysqli_prepare($db, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ii", $jumlah, $id_buku);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception("Error updating stock: " . mysqli_error($db));
                }
            }
        }
        
        mysqli_commit($db);
        $success = "Peminjaman berhasil disimpan";
        
        // Perbaikan query riwayat peminjaman
        $sql_riwayat = "SELECT dp.id_t_detil_pinjam, dp.qty, 
                               b.nama_buku, b.penulis, b.harga,
                               p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam 
                        FROM t_peminjaman p
                        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
                        WHERE p.id_t_anggota = ? 
                        ORDER BY p.create_date DESC, dp.id_t_detil_pinjam DESC";
        $stmt_riwayat = mysqli_prepare($db, $sql_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $id_anggota);
        mysqli_stmt_execute($stmt_riwayat);
        $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        $error = "Terjadi kesalahan: " . $e->getMessage();
        $result_riwayat = null;
    }
}

include("header.php");
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Input Peminjaman</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal" method="post" id="formPeminjaman">
                        <div class="form-group">
                            <label class="control-label col-sm-2">No Peminjaman</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="no_peminjaman" value="<?php echo $no_peminjaman; ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-sm-2">Nama Staff</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="nama_staff" value="<?php echo $_SESSION['login_user']; ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-sm-2">Tanggal Pinjam</label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control" name="tgl_pinjam" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label col-sm-2">No Anggota</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="no_anggota" id="noanggota" 
                                       value="<?php echo isset($_POST['no_anggota']) ? $_POST['no_anggota'] : ''; ?>" required>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" name="btncari" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                        
                        <?php if(isset($anggota) && $anggota): ?>
                        <div class="form-group">
                            <label class="control-label col-sm-2">Nama Anggota</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?php echo $anggota['nama']; ?>" readonly>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Daftar Buku yang Akan Dipinjam
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" style="margin-left:15px;" id="check" placeholder="Ketik judul buku...">
                                            <div id="hasilPencarian" style="position: absolute; width: 100%; z-index: 1000;"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form untuk buku yang dipilih -->
                                <div id="formJumlahBuku" style="display: none;" class="form-group">
                                    <div class="row">
                                        <div class="col-md-6" style="margin-left: 20px;">
                                            <label>Judul Buku yang Dipilih:</label>
                                            <p id="judulBukuDipilih" class="form-control-static"></p>
                                            <input type="hidden" id="idBukuDipilih">
                                            <input type="hidden" id="penulisBukuDipilih">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Jumlah:</label>
                                            <input type="number" class="form-control" id="qty" value="1" min="1">
                                        </div>
                                        <div class="col-md-2">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-primary" onclick="tambahKeDaftar()">
                                                <i class="fa fa-plus"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Judul Buku</th>
                                            <th>Jumlah</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="daftar_buku">
                                        <!-- Data buku akan ditampilkan di sini -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tampilkan riwayat peminjaman -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Riwayat Peminjaman</h4>
                </div>
                <div class="panel-body">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Buku</th>
                                <th>Penulis</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(isset($result_riwayat) && mysqli_num_rows($result_riwayat) > 0): 
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                        <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                        <td><?php echo $row['qty']; ?></td>
                                        <td>Rp <?php echo number_format($row['harga'],0,',','.'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="hapusDetailBuku(<?php echo $row['id_t_detil_pinjam']; ?>)">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada riwayat peminjaman</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Fungsi untuk menampilkan hasil pencarian
    $('#check').keyup(function() {
        var keyword = $(this).val();
        
        if(keyword.length > 0) {
            $.ajax({
                url: 'ajax_tambah_buku_pinjam.php',
                type: 'GET',
                data: {
                    action: 'search',
                    keyword: keyword
                },
                success: function(data) {
                    $('#hasilPencarian').html(data);
                    $('#hasilPencarian').show();
                }
            });
        } else {
            $('#hasilPencarian').hide();
        }
    });

    // Sembunyikan dropdown saat klik di luar
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#check, #hasilPencarian').length) {
            $('#hasilPencarian').hide();
        }
    });
});

// Fungsi untuk memilih buku dari dropdown
function pilihBuku(id, judul, penulis) {
    $('#judulBukuDipilih').text(judul + ' (Penulis: ' + penulis + ')');
    $('#idBukuDipilih').val(id);
    $('#penulisBukuDipilih').val(penulis);
    $('#formJumlahBuku').show();
    $('#check').val('');
    $('#hasilPencarian').hide();
}

// Fungsi untuk menambah buku ke daftar
function tambahKeDaftar() {
    var id = $('#idBukuDipilih').val();
    var judul = $('#judulBukuDipilih').text();
    var jumlah = $('#qty').val();
    
    // Validasi jumlah
    if(jumlah < 1) {
        alert('Jumlah buku minimal 1');
        return;
    }
    
    // Cek apakah buku sudah ada di daftar
    var bukuSudahAda = false;
    $('input[name="id_buku[]"]').each(function() {
        if ($(this).val() == id) {
            bukuSudahAda = true;
            alert('Buku ini sudah ada dalam daftar peminjaman!');
            return false;
        }
    });
    
    if (!bukuSudahAda) {
        var row = '<tr>' +
            '<td>' + ($('#daftar_buku tr').length + 1) + '</td>' +
            '<td>' + judul + '</td>' +
            '<td>' + jumlah + '</td>' +
            '<td>' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="hapusBuku(this)">' +
            '<i class="fa fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '<td>' +
            '<input type="hidden" name="id_buku[]" value="' + id + '">' +
            '<input type="hidden" name="jumlah_buku[]" value="' + jumlah + '">' +
            '</td>' +
            '</tr>';
        
        $('#daftar_buku').append(row);
    }
    
    // Reset form
    $('#formJumlahBuku').hide();
    $('#judulBukuDipilih').text('');
    $('#idBukuDipilih').val('');
    $('#penulisBukuDipilih').val('');
    $('#qty').val(1);
    
    // Tampilkan tombol Simpan
    if(!$('#btnSimpanPeminjaman').length) {
        $('#formPeminjaman').append(
            '<div class="form-group mt-3">' +
            '<button type="button" id="btnSimpanPeminjaman" class="btn btn-primary" onclick="simpanPeminjaman()">' +
            '<i class="fa fa-save"></i> Simpan Peminjaman' +
            '</button>' +
            '</div>'
        );
    }
}

// Tambahkan fungsi untuk simpan peminjaman
function simpanPeminjaman() {
    // Validasi anggota terlebih dahulu
    var noAnggota = $('#noanggota').val();
    
    if(!noAnggota) {
        alert('Pilih anggota terlebih dahulu!');
        return false;
    }

    // Validasi ada buku yang dipilih
    if($('#daftar_buku tr').length === 0) {
        alert('Pilih buku terlebih dahulu!');
        return false;
    }

    // Tambahkan input hidden untuk tanggal kembali jika belum ada
    if($('input[name="tgl_kembali"]').length === 0) {
        var tglKembali = new Date();
        tglKembali.setDate(tglKembali.getDate() + 7); // Default 7 hari
        $('#formPeminjaman').append('<input type="hidden" name="tgl_kembali" value="' + tglKembali.toISOString().split('T')[0] + '">');
    }

    // Submit form
    $('#formPeminjaman').append('<input type="hidden" name="simpan_peminjaman" value="1">');
    $('#formPeminjaman').submit();
}

// Tambahkan fungsi untuk cek anggota saat tombol Cari diklik
function cariAnggota() {
    var noAnggota = $('#no_anggota').val();
    if(!noAnggota) {
        alert('Masukkan nomor anggota!');
        return false;
    }

    $.ajax({
        url: 'cari_anggota.php',
        type: 'POST',
        data: {
            no_anggota: noAnggota
        },
        success: function(response) {
            if(response.nama) {
                $('#nama_anggota').val(response.nama);
            } else {
                alert('Anggota tidak ditemukan!');
                $('#nama_anggota').val('');
            }
        }
    });
}

// Fungsi untuk hapus buku dari daftar
function hapusBuku(btn) {
    $(btn).closest('tr').remove();
    // Perbarui nomor urut
    $('#daftar_buku tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
    
    // Sembunyikan tombol Simpan jika tidak ada buku
    if($('#daftar_buku tr').length === 0) {
        $('#btnSimpanPeminjaman').parent().remove();
    }
}

// Tambahkan fungsi untuk refresh riwayat setelah simpan
function refreshRiwayat() {
    if($('#noanggota').val()) {
        $.ajax({
            url: 'ajax_riwayat_peminjaman.php',
            type: 'POST',
            data: {
                no_anggota: $('#noanggota').val()
            },
            success: function(response) {
                $('#riwayat_peminjaman tbody').html(response);
            }
        });
    }
}
</script>

<style>
#hasilPencarian {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}
#hasilPencarian a {
    display: block;
    padding: 8px 15px;
    color: #333;
    text-decoration: none;
}
#hasilPencarian a:hover {
    background-color: #f5f5f5;
}

.list-group-item {
    padding: 10px 15px;
    border: 1px solid #ddd;
    margin-bottom: -1px;
}

.list-group-item:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.badge {
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #000;
}

.text-muted {
    color: #6c757d;
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}
</style>

<?php 
// include "../template/footer.php" 
?>