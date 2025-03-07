<?php
session_start();
require_once '../setting/koneksi.php';

// Set header JSON
header('Content-Type: application/json');

if(!isset($_SESSION['login_user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Silakan login terlebih dahulu'
    ]);
    exit;
}

// Validasi input
if(!isset($_POST['id_rating']) || !isset($_POST['jenis'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

// Validasi jenis like/dislike
if($_POST['jenis'] !== 'like' && $_POST['jenis'] !== 'dislike') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Jenis tidak valid'
    ]);
    exit;
}

$id_rating = (int)$_POST['id_rating'];
$jenis = $_POST['jenis'];
$username = $_SESSION['login_user'];

try {
    // Cek apakah user sudah like/dislike sebelumnya
    $check_sql = "SELECT jenis FROM t_rating_like 
                  WHERE id_rating = ? AND username = ?";
    $check_stmt = mysqli_prepare($db, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "is", $id_rating, $username);
    mysqli_stmt_execute($check_stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));

    if($existing) {
        if($existing['jenis'] === $jenis) {
            // Hapus like/dislike jika user klik tombol yang sama
            $sql = "DELETE FROM t_rating_like 
                    WHERE id_rating = ? AND username = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "is", $id_rating, $username);
        } else {
            // Update jenis jika user mengubah pilihan
            $sql = "UPDATE t_rating_like 
                    SET jenis = ? 
                    WHERE id_rating = ? AND username = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "sis", $jenis, $id_rating, $username);
        }
    } else {
        // Insert baru jika belum pernah like/dislike
        $sql = "INSERT INTO t_rating_like (id_rating, username, jenis) 
                VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $id_rating, $username, $jenis);
    }
    
    mysqli_stmt_execute($stmt);

    // Ambil jumlah like dan dislike terbaru
    $count_sql = "SELECT 
                    SUM(CASE WHEN jenis = 'like' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN jenis = 'dislike' THEN 1 ELSE 0 END) as dislikes
                  FROM t_rating_like 
                  WHERE id_rating = ?";
    $count_stmt = mysqli_prepare($db, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "i", $id_rating);
    mysqli_stmt_execute($count_stmt);
    $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));

    echo json_encode([
        'status' => 'success',
        'likes' => (int)$counts['likes'],
        'dislikes' => (int)$counts['dislikes']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 