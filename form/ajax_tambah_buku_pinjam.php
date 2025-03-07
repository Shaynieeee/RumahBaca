<?php
// Tampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../setting/koneksi.php';

// Debug
error_log("=== DEBUG SESSION ===");
error_log(print_r($_SESSION, true));
error_log("=== DEBUG POST ===");
error_log(print_r($_POST, true));

if(!isset($_SESSION['login_user'])) {
    die("Akses ditolak");
}

$usersession = $_SESSION['login_user'];
$role = $_SESSION['role'];

if(isset($_GET['action']) && $_GET['action'] == 'search') {
    $keyword = mysqli_real_escape_string($db, $_GET['keyword']);
    
    $sql = "SELECT id_t_buku, nama_buku, penulis, stok 
            FROM t_buku 
            WHERE (nama_buku LIKE ? OR penulis LIKE ?) 
            AND stok > 0 
            ORDER BY nama_buku ASC 
            LIMIT 10";
            
    $stmt = mysqli_prepare($db, $sql);
    $param = "%$keyword%";
    mysqli_stmt_bind_param($stmt, "ss", $param, $param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="list-group">';
        while($row = mysqli_fetch_assoc($result)) {
            echo '<a href="javascript:void(0)" class="list-group-item" 
                    onclick="pilihBuku(' . $row['id_t_buku'] . ', \'' . addslashes($row['nama_buku']) . '\', \'' . addslashes($row['penulis']) . '\')">';
            echo '<div class="d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<strong>' . $row['nama_buku'] . '</strong><br>';
            echo '<small>Penulis: ' . $row['penulis'] . '</small>';
            echo '</div>';
            echo '<span class="badge ' . ($row['stok'] <= 3 ? 'badge-warning' : 'badge-info') . '">';
            echo 'Stok: ' . $row['stok'];
            echo '</span>';
            echo '</div>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<div class="list-group-item">Tidak ada buku yang ditemukan</div>';
    }
}

if(isset($_POST['id_buku']) && isset($_POST['jumlah'])) {
    try {
        $id_buku = mysqli_real_escape_string($db, $_POST['id_buku']);
        $jumlah = mysqli_real_escape_string($db, $_POST['jumlah']);
        $no_anggota = mysqli_real_escape_string($db, $_POST['no_anggota']);
        
        // Dapatkan id_anggota dari no_anggota
        $sql_anggota = "SELECT id_t_anggota FROM t_anggota WHERE no_anggota = ?";
        $stmt = mysqli_prepare($db, $sql_anggota);
        mysqli_stmt_bind_param($stmt, "s", $no_anggota);
        mysqli_stmt_execute($stmt);
        $result_anggota = mysqli_stmt_get_result($stmt);
        $row_anggota = mysqli_fetch_assoc($result_anggota);
        $id_anggota = $row_anggota['id_t_anggota'];

        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
        $status = 'Dipinjam';
        $create_date = date('Y-m-d');
        $create_by = substr($usersession, 0, 3);

        // Generate nomor peminjaman
        $tahun = date('Y');
        $bulan = date('m');
        $no_pinjam = "PJM" . $tahun . $bulan . sprintf("%03d", rand(1, 999));

        // Query untuk admin (role = 1)
        if($role == 1) {
            $sql = "INSERT INTO t_peminjaman (no_peminjaman, id_t_anggota, tgl_pinjam, tgl_kembali, status, create_date, create_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $no_pinjam, $id_anggota, $tgl_pinjam, $tgl_kembali, $status, $create_date, $create_by);
        } 
        // Query untuk staff (role = 2)
        else {
            $sql_staff = "SELECT id_t_staff FROM t_staff s 
                         JOIN t_account a ON s.id_t_account = a.id_t_account 
                         WHERE a.username = ?";
            $stmt = mysqli_prepare($db, $sql_staff);
            mysqli_stmt_bind_param($stmt, "s", $usersession);
            mysqli_stmt_execute($stmt);
            $result_staff = mysqli_stmt_get_result($stmt);
            $row_staff = mysqli_fetch_assoc($result_staff);
            $id_staff = $row_staff['id_t_staff'];

            $sql = "INSERT INTO t_peminjaman (no_peminjaman, id_t_staff, id_t_anggota, tgl_pinjam, tgl_kembali, status, create_date, create_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssss", $no_pinjam, $id_staff, $id_anggota, $tgl_pinjam, $tgl_kembali, $status, $create_date, $create_by);
        }

        mysqli_stmt_execute($stmt);
        $id_peminjaman = mysqli_insert_id($db);
            
        // Insert detail peminjaman
        $sql_detail = "INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, qty) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql_detail);
        mysqli_stmt_bind_param($stmt, "iii", $id_peminjaman, $id_buku, $jumlah);
        mysqli_stmt_execute($stmt);
            
        // Tampilkan data buku
        $sql_view = "SELECT b.nama_buku, d.qty 
                    FROM t_detil_pinjam d 
                    JOIN t_buku b ON d.id_t_buku = b.id_t_buku 
                    WHERE d.id_t_peminjaman = ?";
        $stmt = mysqli_prepare($db, $sql_view);
        mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
        mysqli_stmt_execute($stmt);
        $result_view = mysqli_stmt_get_result($stmt);
                
        $output = "";
        $no = 1;
        while($row = mysqli_fetch_assoc($result_view)) {
            $output .= "<tr>";
            $output .= "<td>".$no++."</td>";
            $output .= "<td>".$row['nama_buku']."</td>";
            $output .= "<td>".$row['qty']."</td>";
            $output .= "<td><button type='button' class='btn btn-danger btn-sm' onclick='hapusBuku(".$id_peminjaman.")'><i class='fa fa-trash'></i></button></td>";
            $output .= "</tr>";
        }
                
        echo $output;
            
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
?> 