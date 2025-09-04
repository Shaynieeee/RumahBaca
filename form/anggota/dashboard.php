<?php
session_start();
require_once '../../setting/koneksi.php';

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

include("header_anggota.php");
?>

<div id="page-wrapper">
    <!-- Hero Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2>Selamat datang, <?php echo htmlspecialchars($anggota['nama']); ?></h2>
                    <h1 class="mt-4 mb-5">Temukan Buku Favoritmu</h1>

                    <!-- Form Pencarian -->
                    <div class="search-container">
                        <form action="data_buku.php" method="GET" class="d-flex justify-content-center">
                            <div class="input-group" style="max-width: 800px;">
                                <input type="text" name="txtJudul" class="form-control form-control-lg"
                                    placeholder="Cari judul, pengarang..." style="border-radius: 5px 0 0 5px;">
                                <select name="txtKategori" class="form-control form-control-lg"
                                    style="max-width: 250px; border-radius: 0;">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    if ($result_kategori) {
                                        while ($row_kategori = mysqli_fetch_assoc($result_kategori)) {
                                            echo "<option value='" . htmlspecialchars($row_kategori['nama_kategori']) . "'>" . htmlspecialchars($row_kategori['nama_kategori']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-lg"
                                        style="border-radius: 0 5px 5px 0;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Buku Terbaru -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <h3 class="mb-4"><i class="fa fa-book"></i> Buku Terbaru</h3>
                    <div class="row">
                        <?php
                        if ($result_buku && mysqli_num_rows($result_buku) > 0) {
                            while ($row = mysqli_fetch_assoc($result_buku)) {
                                $gambar_id = $row['gambar'] ?? '';
                                $gambar_path = "../../image/buku/default.jpg"; // Default image
                        
                                if (!empty($gambar_id)) {
                                    $files = glob("../../image/buku/{$gambar_id}*");
                                    if (!empty($files)) {
                                        $gambar_path = $files[0];
                                    }
                                }
                                ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <img src="<?php echo htmlspecialchars($gambar_path); ?>" class="card-img-top"
                                            alt="<?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>"
                                            style="height: 250px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-truncate">
                                                <?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>
                                            </h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i>
                                                    <?php echo htmlspecialchars($row['penulis'] ?? 'Tidak ada penulis'); ?><br>
                                                    <i class="fa fa-calendar"></i>
                                                    <?php echo htmlspecialchars($row['tahun_terbit'] ?? 'Tidak ada tahun'); ?>
                                                </small>
                                            </p>
                                            <div class="mt-2">
                                                <?php if (!empty($row['file_buku'])): ?>
                                                    <?php if ($row['stok'] > 0): ?>
                                                        <span class="badge badge-success">Tersedia Offline</span>
                                                        <span class="badge badge-info">Tersedia Online</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info">Tersedia Hanya Online</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if ($row['stok'] > 0): ?>
                                                        <span class="badge badge-success">Tersedia Offline</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn-group mt-auto w-100">
                                                <a href="detail-buku.php?id=<?php echo (int) $row['id_t_buku']; ?>"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fa fa-info-circle"></i> Detail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p class='text-center'>Tidak ada buku terbaru</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .badge {
        padding: 6px 10px;
        margin: 2px;
        font-size: 12px;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-info {
        background-color: #17a2b8;
    }

    .badge-danger {
        background-color: #dc3545;
    }
</style>

<?php include "footer.php"; ?>