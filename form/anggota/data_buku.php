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
if (!isset($_SESSION['login_user']) || !in_array('databuku-member', $scopes)) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota yang login
$username = $_SESSION['login_user'];
$sql_anggota = "SELECT a.* FROM t_anggota a 
                JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
                WHERE acc.username = ?";
$stmt = mysqli_prepare($db, $sql_anggota);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result_anggota = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result_anggota);

include("header_anggota.php");
?>

<div id="page-wrapper">
    <!-- Profile Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fa fa-user-circle fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">Member sejak: <?php echo date('d F Y', strtotime($anggota['create_date'])); ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" name="txtJudul" class="form-control"
                                        placeholder="Cari judul buku..."
                                        value="<?php echo isset($_GET['txtJudul']) ? htmlspecialchars($_GET['txtJudul']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="txtKategori" class="form-control">
                                        <option value="">Semua Kategori</option>
                                        <?php
                                        // Gunakan tabel t_kategori_buku untuk kategori
                                        $sql_kategori = "SELECT nama_kategori FROM t_kategori_buku ORDER BY nama_kategori";
                                        $result_kategori = mysqli_query($db, $sql_kategori);
                                        while ($row_kategori = mysqli_fetch_assoc($result_kategori)) {
                                            $selected = (isset($_GET['txtKategori']) && $_GET['txtKategori'] == $row_kategori['nama_kategori']) ? 'selected' : '';
                                            echo "<option value='" . $row_kategori['nama_kategori'] . "' $selected>" . $row_kategori['nama_kategori'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select name="txtKetersediaan" class="form-control">
                                        <option value="">Semua Ketersediaan</option>
                                        <option value="online" <?php echo (isset($_GET['txtKetersediaan']) && $_GET['txtKetersediaan'] == 'online') ? 'selected' : ''; ?>>Buku Online
                                        </option>
                                        <option value="offline" <?php echo (isset($_GET['txtKetersediaan']) && $_GET['txtKetersediaan'] == 'offline') ? 'selected' : ''; ?>>Buku Offline
                                        </option>
                                        <option value="both" <?php echo (isset($_GET['txtKetersediaan']) && $_GET['txtKetersediaan'] == 'both') ? 'selected' : ''; ?>>Online & Offline
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- ADDED: txtStok input -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="number" name="txtStok" min="0" class="form-control" placeholder="Min. stok"
                                           value="<?php echo isset($_GET['txtStok']) ? (int)$_GET['txtStok'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="row">
        <?php
        // Query untuk mencari buku
        $where = "WHERE 1=1";
        if (isset($_GET['txtJudul']) && !empty($_GET['txtJudul'])) {
            $where .= " AND nama_buku LIKE '%" . mysqli_real_escape_string($db, $_GET['txtJudul']) . "%'";

        }
        if (isset($_GET['txtKategori']) && !empty($_GET['txtKategori'])) {
            $where .= " AND jenis = '" . mysqli_real_escape_string($db, $_GET['txtKategori']) . "'";
        }
        if (isset($_GET['txtKetersediaan']) && !empty($_GET['txtKetersediaan'])) {
            switch ($_GET['txtKetersediaan']) {
                case 'online':
                    $where .= " AND file_buku IS NOT NULL AND file_buku != '' ";
                    break;
                case 'offline':
                    $where .= " AND stok > 0 ";
                    break;
                case 'both':
                    $where .= " AND ((file_buku IS NOT NULL AND file_buku != '') AND stok > 0) ";
                    break;
            }
        }

        // ADDED: filter txtStok (minimum stock)
        if(isset($_GET['txtStok']) && $_GET['txtStok'] !== '') {
            $stok_min = (int) $_GET['txtStok'];
            if($stok_min > 0) {
                $where .= " AND stok >= $stok_min ";
            }
        }

        // Pagination
        $limit = 12; // 3x4 grid
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        $sql = "SELECT * FROM t_buku $where ORDER BY nama_buku LIMIT $start, $limit";
        $result = mysqli_query($db, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <?php
                    // Cari file gambar yang sesuai
                    $gambar_id = $row['gambar'] ?? '';
                    $gambar_path = "../../image/buku/default.jpg"; // Default image
                
                    if (!empty($gambar_id)) {
                        // Coba cari file dengan pola nama yang sesuai
                        $files = glob("../../image/buku/{$gambar_id}*");
                        if (!empty($files)) {
                            $gambar_path = $files[0]; // Ambil file pertama yang ditemukan
                        } else {
                            // Jika tidak ditemukan, coba cari dengan pola lain
                            $files2 = glob("../../image/buku/*{$gambar_id}*");
                            if (!empty($files2)) {
                                $gambar_path = $files2[0];
                            }
                        }
                    }
                    ?>
                    <img src="<?php echo $gambar_path; ?>" class="card-img-top"
                        alt="<?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>"
                        style="height: 250px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">
                            <?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?></h5>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fa fa-user"></i>
                                <?php echo htmlspecialchars($row['penulis'] ?? 'Tidak ada penulis'); ?><br>
                                <i class="fa fa-calendar"></i>
                                <?php echo htmlspecialchars($row['tahun_terbit'] ?? 'Tidak ada tahun'); ?>
                            </small>
                        </p>
                        <div class="mt-2">
                            <?php if(!empty($row['file_buku'])): ?>
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge badge-success">Tersedia Offline </span>
                                    <span class="badge badge-info">Stok buku : <?php echo (int)$row['stok']; ?> </span>
                                    <span class="badge badge-info">Tersedia Online</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Tersedia Hanya Online</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($row['stok'] > 0): ?>
                                    <span class="badge badge-success">Tersedia Offline</span>
                                    <span class="badge badge-info">Stok buku : <?php echo (int)$row['stok']; ?> </span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Tidak Tersedia</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group mt-auto w-100">
                            <a href="detail-buku.php?id=<?php echo $row['id_t_buku']; ?>" class="btn btn-info btn-sm">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Pagination -->
    <div class="row">
        <div class="col-lg-12">
            <?php
            $sql_count = "SELECT COUNT(*) as total FROM t_buku $where";
            $result_count = mysqli_query($db, $sql_count);
            $row_count = mysqli_fetch_assoc($result_count);
            $total_pages = ceil($row_count['total'] / $limit);

            if ($total_pages > 1) {
                echo '<ul class="pagination justify-content-center">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active'>";
                    echo "<a class='page-link' href='?page=$i";
                    if(isset($_GET['txtJudul'])) echo "&txtJudul=".urlencode($_GET['txtJudul']);
                    if(isset($_GET['txtKategori'])) echo "&txtKategori=".urlencode($_GET['txtKategori']);
                    if(isset($_GET['txtKetersediaan'])) echo "&txtKetersediaan=".urlencode($_GET['txtKetersediaan']);
                    if(isset($_GET['txtStok'])) echo "&txtStok=".urlencode((int)$_GET['txtStok']);
                    echo "'>$i</a></li>";
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .card {
        transition: transform 0.2s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .card-img-top {
        border-bottom: 1px solid #eee;
    }

    .btn-group {
        margin-top: auto;
    }

    .pagination {
        margin-top: 2rem;
    }

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

<?php include("footer.php"); ?>