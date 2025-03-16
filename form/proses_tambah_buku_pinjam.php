<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../setting/koneksi.php';
require_once '../setting/session.php';

header('Content-Type: application/json');
$response = array();

if(!isset($_SESSION['login_user'])) {
    header("location:../index.php");
    exit;
}

$usersession = $_SESSION['login_user'];

// Cek role dari t_account
$sql_role = "SELECT a.id_p_role, COALESCE(s.id_t_staff, 0) as id_staff 
             FROM t_account a 
             LEFT JOIN t_staff s ON a.id_t_account = s.id_t_account 
             WHERE a.username = '$usersession'";
$result_role = mysqli_query($db, $sql_role);
$row_role = mysqli_fetch_assoc($result_role);

if (!$row_role) {
    echo "<script>
            alert('User tidak ditemukan!');
            window.location.href='input_peminjaman.php';
          </script>";
    exit;
}

$role_id = $row_role['id_p_role'];
$id_staff = $row_role['id_staff'];

if(isset($_POST['submit'])) {
    try {
        mysqli_begin_transaction($db);
        
        $id_anggota = $_POST['id_anggota'];
        $no_peminjaman = $_POST['no_peminjaman'];
        $tgl_pinjam = $_POST['tgl_pinjam'];
        $tgl_kembali = $_POST['tgl_kembali'];
        $id_t_staff = $_POST['id_t_staff'];
        $buku_array = json_decode($_POST['buku']);
        $user = $_SESSION['login_user'];
        
        // Insert ke t_peminjaman
        $sql_pinjam = "INSERT INTO t_peminjaman (no_peminjaman, id_t_anggota, id_t_staff, 
                       tgl_pinjam, tgl_kembali, status, create_by, create_date) 
                       VALUES (?, ?, ?, ?, ?, 'Dipinjam', ?, NOW())";
        $stmt = mysqli_prepare($db, $sql_pinjam);
        mysqli_stmt_bind_param($stmt, "siisss", $no_peminjaman, $id_anggota, $id_t_staff,
                             $tgl_pinjam, $tgl_kembali, $user);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting peminjaman: " . mysqli_error($db));
        }
        
        $id_peminjaman = mysqli_insert_id($db);
        
        // Insert setiap buku ke t_detil_pinjam
        foreach($buku_array as $id_buku) {
            // Cek stok buku
            $sql_stok = "SELECT stok FROM t_buku WHERE id_t_buku = ? AND stok > 0";
            $stmt_stok = mysqli_prepare($db, $sql_stok);
            mysqli_stmt_bind_param($stmt_stok, "i", $id_buku);
            mysqli_stmt_execute($stmt_stok);
            $result_stok = mysqli_stmt_get_result($stmt_stok);
            
            if(mysqli_num_rows($result_stok) > 0) {
                // Insert detail peminjaman
                $sql_detail = "INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, 
                              kondisi, create_by, create_date) 
                              VALUES (?, ?, 'Baik', ?, NOW())";
                $stmt_detail = mysqli_prepare($db, $sql_detail);
                mysqli_stmt_bind_param($stmt_detail, "iis", $id_peminjaman, 
                                     $id_buku, $user);
                
                if (!mysqli_stmt_execute($stmt_detail)) {
                    throw new Exception("Error inserting detail: " . mysqli_error($db));
                }
                
                // Update stok buku
                $sql_update = "UPDATE t_buku SET stok = stok - 1 WHERE id_t_buku = ?";
                $stmt_update = mysqli_prepare($db, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "i", $id_buku);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception("Error updating stock: " . mysqli_error($db));
                }
            }
        }
        
        mysqli_commit($db);
        $response['status'] = 'success';
        $response['message'] = 'Data peminjaman berhasil disimpan';
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request';
}

// Jika ini adalah permintaan pencarian buku
if(isset($_GET['action']) && $_GET['action'] == 'search') {
    $keyword = mysqli_real_escape_string($db, $_GET['keyword']);
    
    $sql = "SELECT id_t_buku, nama_buku, penulis, stok 
            FROM t_buku 
            WHERE (nama_buku LIKE ? OR penulis LIKE ?) 
            AND stok > 0 
            AND status = 'Aktif'";
            
    $stmt = mysqli_prepare($db, $sql);
    $param = "%$keyword%";
    mysqli_stmt_bind_param($stmt, "ss", $param, $param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="list-group">';
        while($row = mysqli_fetch_assoc($result)) {
            echo '<a href="javascript:void(0)" class="list-group-item" 
                    onclick="pilihBuku('.$row['id_t_buku'].',\''.addslashes($row['nama_buku']).'\',\''.addslashes($row['penulis']).'\')">';
            echo '<div class="d-flex justify-content-between">';
            echo '<div><strong>'.$row['nama_buku'].'</strong><br>';
            echo '<small class="text-muted">Penulis: '.$row['penulis'].'</small></div>';
            echo '<span class="badge badge-info">Stok: '.$row['stok'].'</span>';
            echo '</div></a>';
        }
        echo '</div>';
    } else {
        echo '<div class="list-group-item">Tidak ada buku yang ditemukan</div>';
    }
    exit; // Penting! Keluar agar tidak menjalankan kode di bawah
}

// Jika bukan permintaan pencarian, tampilkan form pencarian buku
?>

<div class="form-group">
    <div class="row">
        <div class="col-md-8">
            <input type="text" class="form-control" id="check" placeholder="Ketik judul buku...">
            <div id="hasilPencarian" style="position: absolute; width: 100%; z-index: 1000; display: none;"></div>
        </div>
    </div>
</div>

<!-- Form untuk buku yang dipilih -->
<div id="formJumlahBuku" style="display: none;" class="form-group">
    <div class="row">
        <div class="col-md-6">
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
            <th style="display:none;">Hidden</th>
        </tr>
    </thead>
    <tbody id="daftar_buku">
        <!-- Data buku akan ditampilkan di sini -->
    </tbody>
</table>

<script type="text/javascript">
$(document).ready(function() {
    // Fungsi untuk menampilkan hasil pencarian
    $('#check').keyup(function() {
        var keyword = $(this).val();
        
        if(keyword.length > 0) {
            $.ajax({
                url: 'proses_tambah_buku_pinjam.php',
                type: 'GET',
                data: {
                    action: 'search',
                    keyword: keyword
                },
                success: function(data) {
                    $('#hasilPencarian').html(data).show();
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
</script>

<?php
echo json_encode($response);
?> 