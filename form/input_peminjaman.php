<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

$error = "";
$success = "";
$result_riwayat = null; // Inisialisasi variabel

// Mendapatkan id_staff/admin
if (isset($_SESSION['login_user'])) {
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

// Fungsi untuk debugging
function debug_to_console($data)
{
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug: " . addslashes($output) . "');</script>";
}

// Cek struktur tabel
$check_tables = mysqli_query($db, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($check_tables)) {
    $tables[] = $row[0];
}
debug_to_console("Tables: " . implode(", ", $tables));

// Cek struktur tabel t_peminjaman
$check_columns = mysqli_query($db, "SHOW COLUMNS FROM t_peminjaman");
$columns = [];
while ($row = mysqli_fetch_array($check_columns)) {
    $columns[] = $row[0];
}
debug_to_console("t_peminjaman columns: " . implode(", ", $columns));

// Cek struktur tabel t_detil_pinjam
$check_columns = mysqli_query($db, "SHOW COLUMNS FROM t_detil_pinjam");
$columns = [];
while ($row = mysqli_fetch_array($check_columns)) {
    $columns[] = $row[0];
}
debug_to_console("t_detil_pinjam columns: " . implode(", ", $columns));

// Cek data peminjaman yang ada
$check_data = mysqli_query($db, "SELECT COUNT(*) as count FROM t_peminjaman");
$row = mysqli_fetch_assoc($check_data);
debug_to_console("Total peminjaman: " . $row['count']);

$check_data = mysqli_query($db, "SELECT COUNT(*) as count FROM t_detil_pinjam");
$row = mysqli_fetch_assoc($check_data);
debug_to_console("Total detail peminjaman: " . $row['count']);

// Jika form tambah buku disubmit
if (isset($_POST['btntambahbuku'])) {
    // Proses penambahan buku ke detail peminjaman
    // ... kode untuk menyimpan detail buku yang dipinjam
}

// Proses simpan peminjaman
if (isset($_POST['simpan_peminjaman'])) {
    try {
        // Cek apakah ada data anggota di session
        if (!isset($_SESSION['selected_anggota'])) {
            throw new Exception("Data anggota tidak ditemukan dalam session");
        }

        mysqli_begin_transaction($db);

        // Mengambil data anggota dari session
        $selected_anggota = $_SESSION['selected_anggota'];

        // Cek apakah id_t_anggota ada
        if (!isset($selected_anggota['id_t_anggota'])) {
            // Coba ambil no_anggota dari session
            $no_anggota = $selected_anggota['no_anggota'] ?? null;

            if (!$no_anggota) {
                throw new Exception("No anggota tidak ditemukan");
            }

            // Cari id_t_anggota berdasarkan no_anggota
            $sql = "SELECT id_t_anggota FROM t_anggota WHERE no_anggota = ? AND status = 'Aktif'";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "s", $no_anggota);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if (!$row) {
                throw new Exception("Anggota dengan nomor $no_anggota tidak ditemukan atau tidak aktif");
            }

            $id_anggota = $row['id_t_anggota'];
        } else {
            $id_anggota = $selected_anggota['id_t_anggota'];
        }

        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = $_POST['tgl_kembali'] ?? date('Y-m-d', strtotime('+7 days'));

        // Validasi tanggal kembali
        if (strtotime($tgl_kembali) < strtotime($tgl_pinjam)) {
            throw new Exception("Tanggal kembali tidak boleh lebih awal dari tanggal pinjam");
        }

        $create_by = substr($_SESSION['login_user'] ?? 'SYS', 0, 3);

        // Set id_staff null jika admin
        $id_staff = ($_SESSION['role'] == 2) ? $_SESSION['id_staff'] : null;

        // Generate nomor peminjaman
        $tahun = date('Y');
        $bulan = date('m');
        $no_peminjaman = "PJM" . $tahun . $bulan . sprintf("%03d", rand(1, 999));

        // 1. Insert ke t_peminjaman
        $sql_pinjam = "INSERT INTO t_peminjaman (no_peminjaman, id_t_anggota, id_t_staff, 
                    tgl_pinjam, tgl_kembali, status, create_date, create_by) 
                    VALUES (?, ?, ?, ?, ?, 'Dipinjam', CURDATE(), ?)";
        $stmt = mysqli_prepare($db, $sql_pinjam);
        mysqli_stmt_bind_param(
            $stmt,
            "siisss",
            $no_peminjaman,
            $id_anggota,
            $id_staff,
            $tgl_pinjam,
            $tgl_kembali,
            $create_by
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting peminjaman: " . mysqli_error($db));
        }

        $id_peminjaman = mysqli_insert_id($db);

        // 2. Insert setiap buku ke t_detil_pinjam (satu baris per eksemplar)
        if (isset($_POST['id_buku']) && is_array($_POST['id_buku'])) {
            foreach ($_POST['id_buku'] as $key => $id_buku) {
                $jumlah = $_POST['jumlah_buku'][$key];
                // Cek stok buku
                $sql_stok = "SELECT stok FROM t_buku WHERE id_t_buku = ?";
                $stmt_stok = mysqli_prepare($db, $sql_stok);
                mysqli_stmt_bind_param($stmt_stok, "i", $id_buku);
                mysqli_stmt_execute($stmt_stok);
                $result_stok = mysqli_stmt_get_result($stmt_stok);
                $row_stok = mysqli_fetch_assoc($result_stok);
                if ($row_stok['stok'] < $jumlah) {
                    throw new Exception("Stok buku tidak mencukupi");
                }
                // Insert satu baris per eksemplar
                for ($i = 0; $i < $jumlah; $i++) {
                    $sql_detail = "INSERT INTO t_detil_pinjam (id_t_peminjaman, id_t_buku, kondisi, create_date, create_by) VALUES (?, ?, 'Baik', CURDATE(), ?)";
                    $stmt_detail = mysqli_prepare($db, $sql_detail);
                    mysqli_stmt_bind_param($stmt_detail, "iis", $id_peminjaman, $id_buku, $create_by);
                    if (!mysqli_stmt_execute($stmt_detail)) {
                        throw new Exception("Error inserting detail: " . mysqli_error($db));
                    }
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

        // Redirect ke halaman yang sama dengan parameter sukses
        header("Location: input_peminjaman.php?success=1&id_anggota=" . $id_anggota);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($db);
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil data riwayat jika ada parameter success dan id_anggota
if (isset($_GET['success']) && isset($_GET['id_anggota'])) {
    $success = "Peminjaman berhasil disimpan";
    $id_anggota = $_GET['id_anggota'];

    // Query untuk mendapatkan data anggota
    $sql_anggota = "SELECT * FROM t_anggota WHERE id_t_anggota = ?";
    $stmt_anggota = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt_anggota, "i", $id_anggota);
    mysqli_stmt_execute($stmt_anggota);
    $result_anggota = mysqli_stmt_get_result($stmt_anggota);
    $anggota = mysqli_fetch_assoc($result_anggota);

    if ($anggota) {
        $_SESSION['selected_anggota'] = $anggota;
    }

    // Query riwayat peminjaman
    $sql_riwayat = "SELECT dp.id_t_detil_pinjam, b.nama_buku, b.penulis, b.harga,
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
}

include("header.php");

// scope check
if (!isset($scopes) || !(in_array('peminjaman', $scopes))) {
    header("Location: ../../landing.php");
    exit;
}
?>

<!-- Tampilkan hasil di bagian atas jika ada success -->
<?php if (!empty($success)): ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Hasil Peminjaman</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-success"><?php echo $success; ?></div>

                <?php if (isset($result_riwayat) && mysqli_num_rows($result_riwayat) > 0): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>Detail Peminjaman Terbaru</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Buku</th>
                                        <th>Penulis</th>
                                        <th>Qty</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                            <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                            <td>1</td>
                                            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                            <a href="input_peminjaman.php" class="btn btn-primary">Buat Peminjaman Baru</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Form peminjaman normal jika tidak ada success -->
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"></h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="panel panel-default">
                    <div class="panel-body">
                        <form class="form-horizontal" method="post" id="formPeminjaman">
                            <div class="form-group">
                                <label class="control-label col-sm-2">No Peminjaman</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="no_peminjaman"
                                        value="<?php echo $no_peminjaman; ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">Nama Staff</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="nama_staff"
                                        value="<?php echo $_SESSION['login_user']; ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">Tanggal Pinjam</label>
                                <div class="col-sm-4">
                                    <input type="date" class="form-control" name="tgl_pinjam"
                                        value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-2">Cari Anggota</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="keyword" id="keyword"
                                        placeholder="Masukkan No Anggota / Nama / No Telepon">
                                </div>
                                <div class="col-sm-2">
                                    <button type="submit" name="btncari" class="btn btn-primary">Cari</button>
                                </div>
                            </div>

                            <?php
                            if (isset($_POST['btncari'])) {
                                $keyword = mysqli_real_escape_string($db, $_POST['keyword']);

                                if (!empty($keyword)) {
                                    $sql = "SELECT id_t_anggota, no_anggota, nama, no_telp, alamat 
                                        FROM t_anggota 
                                        WHERE (no_anggota = ? OR nama LIKE ? OR no_telp = ?) 
                                        AND status = 'Aktif'";

                                    $stmt = mysqli_prepare($db, $sql);
                                    $nama_param = "%$keyword%";
                                    mysqli_stmt_bind_param($stmt, "sss", $keyword, $nama_param, $keyword);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $anggota = mysqli_fetch_assoc($result);

                                    if ($anggota) {
                                        $_SESSION['selected_anggota'] = $anggota;

                                        // Debug anggota yang ditemukan
                                        debug_to_console("Anggota ditemukan: " . json_encode($anggota));

                                        // Ambil riwayat peminjaman untuk anggota ini
                                        $id_anggota = $anggota['id_t_anggota'];

                                        // Debug query riwayat
                                        $query_debug = "SELECT COUNT(*) as count FROM t_peminjaman WHERE id_t_anggota = $id_anggota";
                                        $result_debug = mysqli_query($db, $query_debug);
                                        $row_debug = mysqli_fetch_assoc($result_debug);
                                        debug_to_console("Jumlah peminjaman untuk anggota $id_anggota: " . $row_debug['count']);

                                        // Jika ada peminjaman, cek detail
                                        if ($row_debug['count'] > 0) {
                                            $query_debug = "SELECT p.id_t_peminjaman, p.no_peminjaman, COUNT(dp.id_t_detil_pinjam) as jumlah_buku 
                                                       FROM t_peminjaman p 
                                                       LEFT JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman 
                                                       WHERE p.id_t_anggota = $id_anggota 
                                                       GROUP BY p.id_t_peminjaman";
                                            $result_debug = mysqli_query($db, $query_debug);
                                            while ($row_debug = mysqli_fetch_assoc($result_debug)) {
                                                debug_to_console("Peminjaman #" . $row_debug['no_peminjaman'] . " memiliki " . $row_debug['jumlah_buku'] . " buku");
                                            }
                                        }

                                        // Query riwayat peminjaman
                                        $sql_riwayat = "SELECT dp.id_t_detil_pinjam, b.nama_buku, b.penulis, b.harga,
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

                                        // Debug hasil query
                                        debug_to_console("Query riwayat: " . $sql_riwayat . " dengan id_anggota = " . $id_anggota);
                                        debug_to_console("Jumlah hasil riwayat: " . mysqli_num_rows($result_riwayat));

                                        // Jika tidak ada hasil, coba query alternatif
                                        if (mysqli_num_rows($result_riwayat) == 0) {
                                            debug_to_console("Mencoba query alternatif tanpa JOIN...");

                                            // Query alternatif tanpa JOIN
                                            $sql_alt = "SELECT * FROM t_peminjaman WHERE id_t_anggota = ?";
                                            $stmt_alt = mysqli_prepare($db, $sql_alt);
                                            mysqli_stmt_bind_param($stmt_alt, "i", $id_anggota);
                                            mysqli_stmt_execute($stmt_alt);
                                            $result_alt = mysqli_stmt_get_result($stmt_alt);

                                            debug_to_console("Jumlah peminjaman (query alternatif): " . mysqli_num_rows($result_alt));

                                            // Jika ada peminjaman tapi tidak ada di hasil JOIN, mungkin ada masalah dengan relasi
                                            if (mysqli_num_rows($result_alt) > 0) {
                                                while ($row_alt = mysqli_fetch_assoc($result_alt)) {
                                                    debug_to_console("Peminjaman #" . $row_alt['no_peminjaman'] . " ditemukan, tapi tidak ada di hasil JOIN");

                                                    // Cek detail peminjaman
                                                    $id_peminjaman = $row_alt['id_t_peminjaman'];
                                                    $query_detail = "SELECT COUNT(*) as count FROM t_detil_pinjam WHERE id_t_peminjaman = $id_peminjaman";
                                                    $result_detail = mysqli_query($db, $query_detail);
                                                    $row_detail = mysqli_fetch_assoc($result_detail);
                                                    debug_to_console("Jumlah detail untuk peminjaman #" . $row_alt['no_peminjaman'] . ": " . $row_detail['count']);
                                                }
                                            }
                                        }

                                        // Tampilkan data anggota
                                        ?>
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-8">
                                                <div class="panel panel-info">
                                                    <div class="panel-heading">Data Anggota</div>
                                                    <div class="panel-body">
                                                        <table class="table">
                                                            <tr>
                                                                <td width="150">NIPP</td>
                                                                <td width="20">:</td>
                                                                <td><?php echo htmlspecialchars($anggota['no_anggota']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Nama Anggota</td>
                                                                <td>:</td>
                                                                <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>No Telepon</td>
                                                                <td>:</td>
                                                                <td><?php echo htmlspecialchars($anggota['no_telp']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Alamat</td>
                                                                <td>:</td>
                                                                <td><?php echo htmlspecialchars($anggota['alamat']); ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tambahkan input tanggal kembali -->
                                        <div class="form-group">
                                            <label class="control-label col-sm-2">Tanggal Kembali</label>
                                            <div class="col-sm-4">
                                                <?php
                                                // Set default tanggal kembali 7 hari dari sekarang
                                                $default_tgl_kembali = date('Y-m-d', strtotime('+7 days'));
                                                ?>
                                                <input type="date" class="form-control" name="tgl_kembali" id="tgl_kembali"
                                                    value="<?php echo $default_tgl_kembali; ?>" min="<?php echo date('Y-m-d'); ?>">
                                                <small class="text-muted">Tentukan tanggal pengembalian buku</small>
                                            </div>
                                        </div>

                                        <!-- Panel daftar buku yang akan dipinjam -->
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                Daftar Buku yang Akan Dipinjam
                                            </div>
                                            <div class="panel-body">
                                                <!-- Form pencarian buku -->
                                                <div class="form-group">
                                                    <label class="control-label col-sm-2">Cari Buku</label>
                                                    <div class="col-sm-4">
                                                        <input type="text" class="form-control" id="check"
                                                            placeholder="Masukkan Judul / Penulis Buku">
                                                        <div id="hasilPencarian" class="list-group"
                                                            style="position: absolute; width: 100%; z-index: 1000; display: none;">
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
                                        <?php
                                    } else {
                                        echo "<div class='alert alert-danger'>Data Anggota tidak ditemukan atau tidak aktif</div>";
                                    }
                                }
                            }
                            ?>
                        </form>
                    </div>
                </div>

                <!-- Tampilkan riwayat peminjaman -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Riwayat Peminjaman</h4>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($error)): ?>
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
                                if (isset($result_riwayat) && mysqli_num_rows($result_riwayat) > 0):
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                            <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                                            <td>1</td>
                                            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
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
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function () {
        // Fungsi untuk menampilkan hasil pencarian
        $('#check').keyup(function () {
            var keyword = $(this).val();

            if (keyword.length > 0) {
                $.ajax({
                    url: 'ajax_tambah_buku_pinjam.php',
                    type: 'GET',
                    data: {
                        action: 'search',
                        keyword: keyword
                    },
                    success: function (data) {
                        $('#hasilPencarian').html(data);
                        $('#hasilPencarian').show();
                    }
                });
            } else {
                $('#hasilPencarian').hide();
            }
        });

        // Sembunyikan dropdown saat klik di luar
        $(document).on('click', function (e) {
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
        if (jumlah < 1) {
            alert('Jumlah buku minimal 1');
            return;
        }

        // Cek apakah buku sudah ada di daftar
        var bukuSudahAda = false;
        $('input[name="id_buku[]"]').each(function () {
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
        if (!$('#btnSimpanPeminjaman').length) {
            $('#formPeminjaman').append(
                '<div class="form-group mt-3">' +
                '<button type="button" id="btnSimpanPeminjaman" class="btn btn-primary" onclick="simpanPeminjaman()">' +
                '<i class="fa fa-save"></i> Simpan Peminjaman' +
                '</button>' +
                '</div>'
            );
        }
    }

    // Fungsi untuk simpan peminjaman
    function simpanPeminjaman() {
        // Validasi ada buku yang dipilih
        var jumlahBuku = $('#daftar_buku tr').length;

        if (jumlahBuku === 0) {
            alert('Pilih buku terlebih dahulu!');
            return false;
        }

        // Validasi tanggal kembali
        var tglKembali = $('#tgl_kembali').val();
        var tglPinjam = $('input[name="tgl_pinjam"]').val();

        if (!tglKembali) {
            alert('Tanggal kembali harus diisi!');
            return false;
        }

        // Validasi tanggal kembali tidak boleh kurang dari tanggal pinjam
        if (new Date(tglKembali) < new Date(tglPinjam)) {
            alert('Tanggal kembali tidak boleh lebih awal dari tanggal pinjam!');
            return false;
        }

        // Tambahkan input hidden untuk simpan_peminjaman jika belum ada
        if ($('input[name="simpan_peminjaman"]').length === 0) {
            $('#formPeminjaman').append('<input type="hidden" name="simpan_peminjaman" value="1">');
        }

        // Submit form
        $('#formPeminjaman').submit();
    }

    // Tambahkan fungsi untuk hapus buku dari daftar
    function hapusBuku(btn) {
        $(btn).closest('tr').remove();
        // Perbarui nomor urut
        $('#daftar_buku tr').each(function (index) {
            $(this).find('td:first').text(index + 1);
        });

        // Sembunyikan tombol Simpan jika tidak ada buku
        if ($('#daftar_buku tr').length === 0) {
            $('#btnSimpanPeminjaman').parent().remove();
        }
    }

    // Tambahkan fungsi untuk refresh riwayat setelah simpan
    function refreshRiwayat() {
        if ($('#noanggota').val()) {
            $.ajax({
                url: 'ajax_riwayat_peminjaman.php',
                type: 'POST',
                data: {
                    no_anggota: $('#noanggota').val()
                },
                success: function (response) {
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