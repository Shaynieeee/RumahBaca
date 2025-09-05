<?php
session_start();
include("header_anggota.php");
require_once '../../setting/koneksi.php';

// project root (dua level ke atas dari form/anggota)
$root = dirname(__DIR__, 2);

// Ambil scopes user
$scopes = [];
if (isset($_SESSION['login_user'])) {
    $u = mysqli_real_escape_string($db, $_SESSION['login_user']);
    $sql_s = "SELECT s.name FROM t_account a
              JOIN t_role_scope rs ON a.id_p_role = rs.role_id
              JOIN t_scope s ON rs.scope_id = s.id
              WHERE a.username = '$u'";
    $rs = mysqli_query($db, $sql_s);
    if ($rs) {
        while ($r = mysqli_fetch_assoc($rs))
            $scopes[] = strtolower(trim($r['name']));
    }
}

// akses berdasarkan scope
if (!isset($_SESSION['login_user']) || !in_array('beranda-member', $scopes)) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota
$id_t_anggota = $_SESSION['id_t_anggota'];
$query_anggota = "SELECT * FROM t_anggota WHERE id_t_anggota = ?";
$stmt_anggota = mysqli_prepare($db, $query_anggota);
mysqli_stmt_bind_param($stmt_anggota, "i", $id_t_anggota);
mysqli_stmt_execute($stmt_anggota);
$result_anggota = mysqli_stmt_get_result($stmt_anggota);
$anggota = mysqli_fetch_assoc($result_anggota);

if (!$anggota) {
    session_destroy();
    header("location: ../../login.php?error=invalid_member");
    exit();
}

// Query untuk kategori
$query_kategori = "SELECT * FROM t_kategori_buku ORDER BY nama_kategori";
$result_kategori = mysqli_query($db, $query_kategori);

// Query untuk buku terbaru
$query_buku = "SELECT * FROM t_buku ORDER BY create_date DESC LIMIT 8";
$result_buku = mysqli_query($db, $query_buku);

// Cek dan buat tabel t_kategori_buku jika belum ada
$sql_check_table = "SHOW TABLES LIKE 't_kategori_buku'";
$result_check_table = mysqli_query($db, $sql_check_table);
if (mysqli_num_rows($result_check_table) == 0) {
    // Tabel belum ada, buat tabel baru
    $sql_create_table = "CREATE TABLE IF NOT EXISTS t_kategori_buku (
        id_t_kategori INT AUTO_INCREMENT PRIMARY KEY,
        nama_kategori VARCHAR(100) NOT NULL,
        create_by VARCHAR(50) NOT NULL,
        create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($db, $sql_create_table);

    // Ambil kategori yang sudah ada dari tabel t_buku
    $sql_existing = "SELECT DISTINCT jenis FROM t_buku WHERE jenis IS NOT NULL AND jenis != ''";
    $result_existing = mysqli_query($db, $sql_existing);

    // Tambahkan kategori yang sudah ada ke tabel baru
    if ($result_existing && mysqli_num_rows($result_existing) > 0) {
        while ($row = mysqli_fetch_assoc($result_existing)) {
            $kategori = $row['jenis'];
            $sql_insert = "INSERT INTO t_kategori_buku (nama_kategori, create_by) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            mysqli_stmt_bind_param($stmt, "ss", $kategori, $anggota['username']);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // Tambahkan beberapa kategori default jika tidak ada kategori yang sudah ada
        $default_categories = ['Umum', 'Novel', 'Pendidikan', 'Teknologi', 'Agama'];
        foreach ($default_categories as $category) {
            $sql_insert = "INSERT INTO t_kategori_buku (nama_kategori, create_by) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            mysqli_stmt_bind_param($stmt, "ss", $category, $anggota['username']);
            mysqli_stmt_execute($stmt);
        }
    }
}
?>

<div id="page-wrapper">
    <!-- Hero Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2>Selamat datang, <?php echo htmlspecialchars($anggota['nama']); ?></h2>
                    <h1 class="mt-4 mb-5">Temukan Buku Favoritmu</h1>
<?php 
$catalog_mode = "Terbaru";
include $root . '/components/catalog/catalog.php';
?>
<?php include "footer.php"; ?>