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
<?php
$catalog_mode = "catalog_member";
$root = dirname(__DIR__, 2);
include $root . '/components/catalog/catalog.php';
?>

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
                    if (isset($_GET['txtJudul']))
                        echo "&txtJudul=" . urlencode($_GET['txtJudul']);
                    if (isset($_GET['txtKategori']))
                        echo "&txtKategori=" . urlencode($_GET['txtKategori']);
                    if (isset($_GET['txtKetersediaan']))
                        echo "&txtKetersediaan=" . urlencode($_GET['txtKetersediaan']);
                    if (isset($_GET['txtStok']))
                        echo "&txtStok=" . urlencode((int) $_GET['txtStok']);
                    echo "'>$i</a></li>";
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
