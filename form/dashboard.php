<?php
include("header.php");

$user_check = $_SESSION['login_user'];

$sql = "select id_p_role as role from t_account where username = '$user_check' ";
$result = mysqli_query($db,$sql);
$row = mysqli_fetch_array($result,MYSQLI_ASSOC);

// Query untuk statistik rating
$sql_rating = "SELECT COUNT(*) as JUM from t_rating_buku WHERE DATE(created_date) = CURDATE()";
$result_rating = mysqli_query($db,$sql_rating);
$row_rating = mysqli_fetch_array($result_rating,MYSQLI_ASSOC);

$sql_pj = "select count(*) as JUM from t_peminjaman where create_date = curdate() ";
$result_pj = mysqli_query($db,$sql_pj);
$row_pj = mysqli_fetch_array($result_pj,MYSQLI_ASSOC);

$sql_ag = "select count(*) as JUM from t_anggota where create_date = curdate() ";
$result_ag = mysqli_query($db,$sql_ag);
$row_ag = mysqli_fetch_array($result_ag,MYSQLI_ASSOC);

$sql_bk = "select count(*) as JUM from t_buku where create_date = curdate() ";
$result_bk = mysqli_query($db,$sql_bk);
$row_bk = mysqli_fetch_array($result_bk,MYSQLI_ASSOC);

$sql_jb = "SELECT SUM(JUM) AS JUMLAH,ID FROM v_peminjaman WHERE username = '$user_check'";
$result_jb = mysqli_query($db,$sql_jb);
$row_jb = mysqli_fetch_array($result_jb,MYSQLI_ASSOC);

// Query untuk peminjaman yang mendekati jatuh tempo
$sql_mendekati = "SELECT p.*, a.nama as nama_anggota, a.id_t_account,
                         DATEDIFF(p.tgl_kembali, CURDATE()) as sisa_hari
                  FROM t_peminjaman p
                  JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota
                  LEFT JOIN t_account ac ON a.id_t_account = ac.id_t_account
                  WHERE p.status = 'Dipinjam' 
                  AND p.tgl_kembali >= CURDATE()
                  AND DATEDIFF(p.tgl_kembali, CURDATE()) <= 7
                  ORDER BY p.tgl_kembali ASC";

$result_mendekati = mysqli_query($db, $sql_mendekati);

// Query untuk mendapatkan denda terbaru
$sql_new_denda = "SELECT d.*, p.tgl_kembali, a.nama as nama_anggota, b.nama_buku, 
                         DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat
                  FROM t_denda d 
                  JOIN t_peminjaman p ON d.id_t_peminjaman = p.id_t_peminjaman 
                  JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
                  JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                  JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
                  WHERE d.status_pembayaran = 'Belum Dibayar' 
                  ORDER BY d.create_date DESC";

$result_new_denda = mysqli_query($db, $sql_new_denda);
$count_denda = mysqli_num_rows($result_new_denda);

	// // Query untuk mendapatkan daftar akun yang dinonaktifkan karena telat
	// $sql_nonaktif = "SELECT a.nama, a.no_anggota, a.keterangan 
	//                 FROM t_anggota a 
	//                 WHERE a.status = 'Tidak Aktif' 
	//                 AND a.keterangan LIKE '%Dinonaktifkan sistem%'";
	// $hasil_nonaktif = mysqli_query($db, $sql_nonaktif);
	// $jumlah_nonaktif = mysqli_num_rows($hasil_nonaktif);

// Cek akun nonaktif
if($jumlah_nonaktif > 0) {
    echo '<div class="alert alert-warning">';
    echo '<strong>Perhatian!</strong> Terdapat ' . $jumlah_nonaktif . ' anggota yang dinonaktifkan karena terlambat mengembalikan buku. ';
    echo '<a href="#" data-toggle="modal" data-target="#modalNonaktif">Lihat detail</a>';
    echo '</div>';
    
    // Modal untuk menampilkan detail akun nonaktif
    echo '<div class="modal fade" id="modalNonaktif" tabindex="-1" role="dialog">';
    echo '<div class="modal-dialog" role="document">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<h4 class="modal-title">Daftar Anggota Dinonaktifkan</h4>';
    echo '</div>';
    echo '<div class="modal-body">';
    echo '<table class="table">';
    echo '<thead><tr><th>No Anggota</th><th>Nama</th><th>Keterangan</th></tr></thead>';
    echo '<tbody>';
    while($data = mysqli_fetch_assoc($hasil_nonaktif)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($data['no_anggota']) . '</td>';
        echo '<td>' . htmlspecialchars($data['nama']) . '</td>';
        echo '<td>' . htmlspecialchars($data['keterangan']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '<div class="modal-footer">';
    echo '<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// BAGIAN UTAMA DASHBOARD
if ($row['role']==1 || $row['role']==2) { // admin/staff
?>
    <div id="page-wrapper">
        <!-- Alert untuk peminjaman terlambat - Pindahkan ke sini -->
        <?php
        // Query untuk cek peminjaman terlambat - Update di bagian alert
        $sql_terlambat = "SELECT COUNT(*) as jum_terlambat 
                         FROM t_peminjaman 
                         WHERE (status = 'Belum Kembali' OR 
                              (tgl_kembali < CURDATE() AND status = 'Dipinjam'))";
        $result_terlambat = mysqli_query($db, $sql_terlambat);
        $data_terlambat = mysqli_fetch_assoc($result_terlambat);
        
        if($data_terlambat['jum_terlambat'] > 0) {
        ?>
            <div class="alert alert-danger" style=" border: none; ;">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <span style="color: #a94442;">
                            <strong>Perhatian!</strong> Ada <?php echo $data_terlambat['jum_terlambat']; ?> peminjaman yang belum dikembalikan!
                        </span>
                        <a href="peminjaman_terlambat.php" class="btn btn-danger btn-sm" style="margin-left: 10px;">
                            <i class="fa fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        <?php 
        }
        ?>

        

        <!-- Konten dashboard admin -->
        <div class="row">
            <!-- Peminjaman Hari Ini -->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-book fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $row_pj['JUM'];?></div>
                                <div>Peminjaman Hari Ini</div>
                            </div>
                        </div>
                    </div>
                    <a href="data_peminjaman.php">
                        <div class="panel-footer">
                            <span class="pull-left">Lihat Detail</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Rating & Ulasan -->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-star fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $row_rating['JUM'];?></div>
                                <div style="margin-left:-20px;">Rating & Ulasan Hari Ini</div>
                            </div>
                        </div>
                    </div>
                    <a href="data_rating.php">
                        <div class="panel-footer">
                            <span class="pull-left">Lihat Detail</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Anggota Baru -->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $row_ag['JUM'];?></div>
                                <div>Anggota Baru</div>
                            </div>
                        </div>
                    </div>
                    <a href="data_anggota.php">
                        <div class="panel-footer">
                            <span class="pull-left">Lihat Detail</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Buku Baru -->
            <div class="col-lg-3 col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-book fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $row_bk['JUM'];?></div>
                                <div>Buku Baru</div>
                            </div>
                        </div>
                    </div>
                    <a href="data_buku.php">
                        <div class="panel-footer">
                            <span class="pull-left">Lihat Detail</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Panel Peminjaman Mendekati Jatuh Tempo -->
        <div class="col-lg-12 col-md-12">
            <div class="panel panel-warning">
                <div class="panel-heading" style="background-color: #f0ad4e; color: white; border-color: #f0ad4e;">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-clock-o fa-4x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?php echo mysqli_num_rows($result_mendekati); ?></div>
                            <div>Peminjaman Mendekati Jatuh Tempo</div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <?php if(mysqli_num_rows($result_mendekati) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead style="background: #0067b0; color:#fff;">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Anggota</th>
                                        <th>No Peminjaman</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Sisa Hari</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while($row = mysqli_fetch_assoc($result_mendekati)): 
                                        $tgl_kembali = new DateTime($row['tgl_kembali']);
                                        $today = new DateTime(date('Y-m-d'));
                                        $interval = $today->diff($tgl_kembali);
                                        $sisa_hari = $interval->days;
                                        
                                        // Tentukan warna baris berdasarkan sisa hari
                                        $row_class = '';
                                        if($sisa_hari == 0) {
                                            $row_class = 'danger';
                                        } elseif($sisa_hari == 1) {
                                            $row_class = 'warning';
                                        } elseif($sisa_hari == 2) {
                                            $row_class = 'info';
                                        }
                                    ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_anggota']); ?></td>
                                        <td><?php echo htmlspecialchars($row['no_peminjaman']); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($row['tgl_pinjam'])); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($row['tgl_kembali'])); ?></td>
                                        <td>
                                            <?php 
                                            if($sisa_hari == 0) {
                                                echo '<span class="label label-danger">Hari Ini</span>';
                                            } elseif($sisa_hari == 1) {
                                                echo '<span class="label label-warning">Besok</span>';
                                            } elseif($sisa_hari == 2) {
                                                echo '<span class="label label-info">2 hari lagi</span>';
                                            } else {
                                                echo '<span class="label label-default">' . $sisa_hari . ' hari lagi</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="detil_peminjaman.php?id=<?php echo $row['id_t_peminjaman']; ?>" class="btn btn-info btn-xs">
                                                <i class="fa fa-info-circle"></i> Detail
                                            </a>
                                            <?php if($row['id_t_account']): ?>
                                                <a href="#" onclick="kirimNotifikasi(<?php echo $row['id_t_peminjaman']; ?>)" class="btn btn-warning btn-xs">
                                                    <i class="fa fa-bell"></i> Alert
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Tidak ada peminjaman yang mendekati jatuh tempo dalam 7 hari ke depan.
                        </div>
                    <?php endif; ?>
                </div>
                <!-- <a href="data_peminjaman.php">
                    <div class="panel-footer">
                        <span class="pull-left">Lihat Semua Peminjaman</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a> -->
            </div>
        </div>
        
        <!-- Panel Denda -->
        <?php
        if($count_denda > 0) {
            echo '<div class="alert alert-danger alert-dismissible" role="alert">';
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            echo '<h4><i class="fa fa-warning"></i> Pemberitahuan Denda!</h4>';
            echo '<p>Terdapat ' . $count_denda . ' anggota yang memiliki denda yang belum dibayar. ';
            echo '<a href="#" onclick="toggleDendaTable(); return false;" class="text-danger"><strong>Klik disini</strong></a> untuk melihat detail.</p>';
            echo '</div>';

            // Tabel denda (hidden by default)
            echo '<div id="dendaTableContainer" style="display:none;">';
            echo '<div class="panel panel-danger">';
            echo '<div class="panel-heading">';
            echo '<h3 class="panel-title"><i class="fa fa-money"></i> Daftar Denda Belum Dibayar</h3>';
            echo '</div>';
            echo '<div class="panel-body">';
            echo '<div class="table-responsive">';
            echo '<table class="table table-striped table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>Nama Anggota</th>';
            echo '<th>Judul Buku</th>';
            echo '<th>Tgl Kembali</th>';
            echo '<th>Keterlambatan</th>';
            echo '<th>Total Denda</th>';
            echo '<th>Status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $no = 1;
            while($row_denda = mysqli_fetch_assoc($result_new_denda)) {
                echo "<tr id='denda-row-" . $row_denda['id_t_denda'] . "'>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($row_denda['nama_anggota']) . "</td>";
                echo "<td>" . htmlspecialchars($row_denda['nama_buku']) . "</td>";
                echo "<td>" . date('d-m-Y', strtotime($row_denda['tgl_kembali'])) . "</td>";
                echo "<td>" . $row_denda['hari_terlambat'] . " hari</td>";
                echo "<td>Rp " . number_format($row_denda['jumlah_denda'], 0, ',', '.') . "</td>";
                echo "<td>" . $row_denda['status_pembayaran'] . "</td>";
                echo "</tr>";
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>'; // table-responsive
            echo '</div>'; // panel-body
            echo '</div>'; // panel
            echo '</div>'; // dendaTableContainer
        }
        ?>
        
        <!-- Statistik Perpustakaan -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="background: #0067b0;">
                        <h3 class="panel-title" style="color: #fff;"><i class="fa fa-bar-chart-o fa-fw"></i> Statistik Perpustakaan</h3>
                    </div>
                    <div class="panel-body">
                        <!-- Form Filter -->
                        <form method="get" action="" class="form-inline" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label for="period">Periode:</label>
                                <select name="period" id="period" class="form-control">
                                    <option value="daily" <?php echo (isset($_GET['period']) && $_GET['period'] == 'daily') ? 'selected' : ''; ?>>Harian</option>
                                    <option value="monthly" <?php echo (isset($_GET['period']) && $_GET['period'] == 'monthly') ? 'selected' : ''; ?>>Bulanan</option>
                                    <option value="yearly" <?php echo (isset($_GET['period']) && $_GET['period'] == 'yearly') ? 'selected' : ''; ?>>Tahunan</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="date-filter" style="margin-left: 10px;">
                                <label for="date">Tanggal:</label>
                                <input type="date" name="date" id="date" class="form-control" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group" id="month-filter" style="margin-left: 10px; display: none;">
                                <label for="month">Bulan:</label>
                                <input type="month" name="month" id="month" class="form-control" value="<?php echo isset($_GET['month']) ? $_GET['month'] : date('Y-m'); ?>">
                            </div>
                            
                            <div class="form-group" id="year-filter" style="margin-left: 10px; display: none;">
                                <label for="year">Tahun:</label>
                                <select name="year" id="year" class="form-control">
                                    <?php 
                                    $current_year = date('Y');
                                    for($i = $current_year; $i >= $current_year - 5; $i--) {
                                        $selected = (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin-left: 10px; ">
                                <label for="category">Kategori:</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="all" <?php echo (!isset($_GET['category']) || $_GET['category'] == 'all') ? 'selected' : ''; ?>>Semua</option>
                                    <option value="anggota" <?php echo (isset($_GET['category']) && $_GET['category'] == 'anggota') ? 'selected' : ''; ?>>Anggota</option>
                                    <option value="staff" <?php echo (isset($_GET['category']) && $_GET['category'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                    <option value="buku" <?php echo (isset($_GET['category']) && $_GET['category'] == 'buku') ? 'selected' : ''; ?>>Buku</option>
                                    <option value="peminjaman" <?php echo (isset($_GET['category']) && $_GET['category'] == 'peminjaman') ? 'selected' : ''; ?>>Peminjaman</option>
                                    <option value="rating" <?php echo (isset($_GET['category']) && $_GET['category'] == 'rating') ? 'selected' : ''; ?>>Rating & Ulasan</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Tampilkan</button>
                        </form>
                        
                        <?php
                        // Set default period if not specified
                        $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
                        $category = isset($_GET['category']) ? $_GET['category'] : 'all';
                        
                        // Prepare date conditions based on period
                        if($period == 'daily') {
                            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                            $date_condition = "DATE(create_date) = '$date'";
                            $date_condition_peminjaman = "DATE(create_date) = '$date'";
                            $date_condition_rating = "DATE(created_date) = '$date'";
                            $period_label = "Tanggal " . date('d F Y', strtotime($date));
                        } elseif($period == 'monthly') {
                            $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
                            $year_month = explode('-', $month);
                            $year = $year_month[0];
                            $month_num = $year_month[1];
                            $date_condition = "YEAR(create_date) = $year AND MONTH(create_date) = $month_num";
                            $date_condition_peminjaman = "YEAR(create_date) = $year AND MONTH(create_date) = $month_num";
                            $date_condition_rating = "YEAR(created_date) = $year AND MONTH(created_date) = $month_num";
                            $period_label = "Bulan " . date('F Y', strtotime($month . '-01'));
                        } else { // yearly
                            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                            $date_condition = "YEAR(create_date) = $year";
                            $date_condition_peminjaman = "YEAR(create_date) = $year";
                            $date_condition_rating = "YEAR(created_date) = $year";
                            $period_label = "Tahun " . $year;
                        }
                        
                        // Tampilkan judul periode
                        echo "<h4>Statistik $period_label";
                        if($category != 'all') {
                            echo " - Kategori: " . ucfirst($category);
                        }
                        echo "</h4>";
                        
                        // Inisialisasi array untuk menyimpan data statistik
                        $stats = [
                            'anggota' => 0,
                            'staff' => 0,
                            'buku' => 0,
                            'peminjaman' => 0,
                            'rating' => 0
                        ];
                        
                        // Query untuk statistik anggota
                        if($category == 'all' || $category == 'anggota') {
                            $sql_anggota_stats = "SELECT COUNT(*) as total FROM t_anggota WHERE $date_condition";
                            $result_anggota_stats = mysqli_query($db, $sql_anggota_stats);
                            if($result_anggota_stats) {
                                $row_anggota_stats = mysqli_fetch_array($result_anggota_stats, MYSQLI_ASSOC);
                                $stats['anggota'] = $row_anggota_stats['total'];
                            }
                        }
                        
                        // Query untuk statistik staff
                        if($category == 'all' || $category == 'staff') {
                            $sql_staff_stats = "SELECT COUNT(*) as total FROM t_staff WHERE $date_condition";
                            $result_staff_stats = mysqli_query($db, $sql_staff_stats);
                            if($result_staff_stats) {
                                $row_staff_stats = mysqli_fetch_array($result_staff_stats, MYSQLI_ASSOC);
                                $stats['staff'] = $row_staff_stats['total'];
                            }
                        }
                        
                        // Query untuk statistik buku
                        if($category == 'all' || $category == 'buku') {
                            $sql_buku_stats = "SELECT COUNT(*) as total FROM t_buku WHERE $date_condition";
                            $result_buku_stats = mysqli_query($db, $sql_buku_stats);
                            if($result_buku_stats) {
                                $row_buku_stats = mysqli_fetch_array($result_buku_stats, MYSQLI_ASSOC);
                                $stats['buku'] = $row_buku_stats['total'];
                            }
                        }
                        
                        // Query untuk statistik peminjaman
                        if($category == 'all' || $category == 'peminjaman') {
                            $sql_peminjaman_stats = "SELECT COUNT(*) as total FROM t_peminjaman WHERE $date_condition_peminjaman";
                            $result_peminjaman_stats = mysqli_query($db, $sql_peminjaman_stats);
                            if($result_peminjaman_stats) {
                                $row_peminjaman_stats = mysqli_fetch_array($result_peminjaman_stats, MYSQLI_ASSOC);
                                $stats['peminjaman'] = $row_peminjaman_stats['total'];
                            }
                        }
                        
                        // Query untuk statistik rating
                        if($category == 'all' || $category == 'rating') {
                            $sql_rating_stats = "SELECT COUNT(*) as total FROM t_rating_buku WHERE $date_condition_rating";
                            $result_rating_stats = mysqli_query($db, $sql_rating_stats);
                            if($result_rating_stats) {
                                $row_rating_stats = mysqli_fetch_array($result_rating_stats, MYSQLI_ASSOC);
                                $stats['rating'] = $row_rating_stats['total'];
                            }
                        }
                        
                        // Tampilkan statistik dalam tabel
                        if($category == 'all') {
                            echo '<div class="row">';
                            echo '<div class="col-md-6">';
                            echo '<table class="table table-bordered table-hover">';
                            echo '<thead style="background: #0067b0; color:#fff;"><tr><th>Kategori</th><th>Jumlah</th></tr></thead>';
                            echo '<tbody>';
                            echo '<tr><td>Anggota</td><td>' . $stats['anggota'] . '</td></tr>';
                            echo '<tr><td>Staff</td><td>' . $stats['staff'] . '</td></tr>';
                            echo '<tr><td>Buku</td><td>' . $stats['buku'] . '</td></tr>';
                            echo '<tr><td>Peminjaman</td><td>' . $stats['peminjaman'] . '</td></tr>';
                            echo '<tr><td>Rating & Ulasan</td><td>' . $stats['rating'] . '</td></tr>';
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            
                            // Tampilkan grafik pie
                            echo '<div class="col-md-6">';
                            echo '<div style="height: 300px;">';
                            echo '<canvas id="stats-chart"></canvas>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            // Tampilkan statistik untuk kategori tertentu
                            echo '<div class="row">';
                            echo '<div class="col-md-4">';
                            echo '<table class="table table-bordered table-hover">';
                            echo '<thead><tr><th>Kategori</th><th>Jumlah</th></tr></thead>';
                            echo '<tbody>';
                            echo '<tr><td>' . ucfirst($category) . '</td><td>' . $stats[$category] . '</td></tr>';
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        // Tampilkan grafik detail jika periode bulanan atau tahunan dan kategori tertentu
                        if(($period == 'monthly' || $period == 'yearly') && $category != 'all') {
                            echo '<div class="row">';
                            echo '<div class="col-md-12">';
                            echo '<h4>Detail ' . ucfirst($category) . ' per ' . ($period == 'monthly' ? 'Hari' : 'Bulan') . '</h4>';
                            echo '<div style="height: 300px;">';
                            echo '<canvas id="detail-chart"></canvas>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle filter berdasarkan periode
        const periodSelect = document.getElementById('period');
        const dateFilter = document.getElementById('date-filter');
        const monthFilter = document.getElementById('month-filter');
        const yearFilter = document.getElementById('year-filter');
        
        function toggleFilters() {
            if(periodSelect.value === 'daily') {
                dateFilter.style.display = 'inline-block';
                monthFilter.style.display = 'none';
                yearFilter.style.display = 'none';
            } else if(periodSelect.value === 'monthly') {
                dateFilter.style.display = 'none';
                monthFilter.style.display = 'inline-block';
                yearFilter.style.display = 'none';
            } else { // yearly
                dateFilter.style.display = 'none';
                monthFilter.style.display = 'none';
                yearFilter.style.display = 'inline-block';
            }
        }
        
        toggleFilters();
        
        periodSelect.addEventListener('change', toggleFilters);
        
        // Buat grafik pie untuk statistik keseluruhan
        <?php if($category == 'all'): ?>
        const ctxStats = document.getElementById('stats-chart');
        if(ctxStats) {
            new Chart(ctxStats, {
                type: 'pie',
                data: {
                    labels: ['Anggota', 'Staff', 'Buku', 'Peminjaman', 'Rating & Ulasan'],
                    datasets: [{
                        data: [
                            <?php echo $stats['anggota']; ?>,
                            <?php echo $stats['staff']; ?>,
                            <?php echo $stats['buku']; ?>,
                            <?php echo $stats['peminjaman']; ?>,
                            <?php echo $stats['rating']; ?>
                        ],
                        backgroundColor: [
                            '#5cb85c', // green
                            '#337ab7', // blue
                            '#f0ad4e', // yellow
                            '#d9534f', // red
                            '#5bc0de'  // light blue
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
        <?php endif; ?>
        
        // Buat grafik detail untuk kategori tertentu
        <?php if(($period == 'monthly' || $period == 'yearly') && $category != 'all'): ?>
        <?php
        // Siapkan data untuk grafik detail
        $labels = [];
        $data = [];
        
        if($period == 'monthly') {
            // Data per hari dalam bulan
            $field_name = 'day';
            $date_field = $category == 'rating' ? 'created_date' : 'create_date';
            $sql_detail = "SELECT DAY($date_field) as day, COUNT(*) as total 
                          FROM " . ($category == 'rating' ? 't_rating_buku' : 't_' . $category) . " 
                          WHERE YEAR($date_field) = $year AND MONTH($date_field) = $month_num 
                          GROUP BY DAY($date_field)
                          ORDER BY day";
        } else { // yearly
            // Data per bulan dalam tahun
            $field_name = 'month';
            $date_field = $category == 'rating' ? 'created_date' : 'create_date';
            $sql_detail = "SELECT MONTH($date_field) as month, COUNT(*) as total 
                          FROM " . ($category == 'rating' ? 't_rating_buku' : 't_' . $category) . " 
                          WHERE YEAR($date_field) = $year 
                          GROUP BY MONTH($date_field)
                          ORDER BY month";
        }
        
        $result_detail = mysqli_query($db, $sql_detail);
        
        if($result_detail) {
            if($period == 'monthly') {
                // Data per hari dalam bulan
                $days_in_month = date('t', strtotime($year . '-' . $month_num . '-01'));
                
                // Inisialisasi array dengan 0 untuk semua hari
                for($i = 1; $i <= $days_in_month; $i++) {
                    $labels[] = $i;
                    $data[] = 0;
                }
                
                // Isi data yang ada
                while($row_detail = mysqli_fetch_assoc($result_detail)) {
                    $day = (int)$row_detail['day'];
                    $data[$day-1] = (int)$row_detail['total'];
                }
            } else {
                // Data per bulan dalam tahun
                $month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                
                // Inisialisasi array dengan 0 untuk semua bulan
                for($i = 1; $i <= 12; $i++) {
                    $labels[] = $month_names[$i-1];
                    $data[] = 0;
                }
                
                // Isi data yang ada
                while($row_detail = mysqli_fetch_assoc($result_detail)) {
                    $month = (int)$row_detail['month'];
                    $data[$month-1] = (int)$row_detail['total'];
                }
            }
        }
        ?>
        
        const ctxDetail = document.getElementById('detail-chart');
        if(ctxDetail) {
            new Chart(ctxDetail, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: '<?php echo ucfirst($category); ?>',
                        data: <?php echo json_encode($data); ?>,
                        backgroundColor: '<?php 
                            if($category == 'anggota') echo '#5cb85c';
                            elseif($category == 'staff') echo '#337ab7';
                            elseif($category == 'buku') echo '#f0ad4e';
                            elseif($category == 'peminjaman') echo '#d9534f';
                            elseif($category == 'rating') echo '#5bc0de';
                        ?>',
                        borderColor: '<?php 
                            if($category == 'anggota') echo '#4cae4c';
                            elseif($category == 'staff') echo '#2e6da4';
                            elseif($category == 'buku') echo '#eea236';
                            elseif($category == 'peminjaman') echo '#d43f3a';
                            elseif($category == 'rating') echo '#46b8da';
                        ?>',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    });
    </script>

    <!-- Tambahkan script JavaScript -->
    <script>
    function toggleDendaTable() {
        var container = document.getElementById('dendaTableContainer');
        if(container.style.display === 'none') {
            container.style.display = 'block';
            // Scroll ke tabel
            $('html, body').animate({
                scrollTop: $('#dendaTableContainer').offset().top - 20
            }, 500);
        } else {
            container.style.display = 'none';
        }
    }

    // Jika ada parameter di URL untuk menampilkan tabel denda
    if(window.location.hash === '#showDenda') {
        toggleDendaTable();
    }

    function kirimNotifikasi(id_peminjaman) {
        if(confirm('Apakah Anda yakin ingin mengirim notifikasi?')) {
            $.ajax({
                url: 'kirim_notifikasi_email.php',
                type: 'POST',
                data: {
                    id_peminjaman: id_peminjaman
                },
                success: function(response) {
                    alert('Notifikasi berhasil dikirim');
                },
                error: function() {
                    alert('Gagal mengirim notifikasi');
                }
            });
        }
    }

    // Refresh halaman setiap 5 menit untuk update status
    setInterval(function() {
        location.reload();
    }, 300000);
    </script>

    <!-- Tambahkan style CSS -->
    <style>
    .huge {
        font-size: 40px;
    }

    .panel-green {
        border-color: #5cb85c;
    }

    .panel-green > .panel-heading {
        border-color: #5cb85c;
        color: white;
        background-color: #5cb85c;
    }

    .panel-green > a {
        color: #5cb85c;
    }

    .panel-red {
        border-color: #d9534f;
    }

    .panel-red > .panel-heading {
        border-color: #d9534f;
        color: white;
        background-color: #d9534f;
    }

    .panel-red > a {
        color: #d9534f;
    }

    .panel-yellow {
        border-color: #f0ad4e;
    }

    .panel-yellow > .panel-heading {
        border-color: #f0ad4e;
        color: white;
        background-color: #f0ad4e;
    }

    .panel-yellow > a {
        color: #f0ad4e;
    }

    .panel-footer {
        padding: 10px 15px;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
    }

    .panel-footer:hover {
        background-color: #eee;
    }

    .highlight-row {
        background-color: #fff3cd !important;
        transition: background-color 0.5s ease;
    }

    .alert a {
        text-decoration: none;
    }

    .alert a:hover {
        text-decoration: underline;
    }

    #dendaTableContainer {
        margin-bottom: 20px;
    }

    .panel-danger > .panel-heading {
        color: #fff;
        background-color: #d9534f;
        border-color: #d43f3a;
    }

    .panel-danger > .panel-heading .panel-title {
        font-size: 16px;
    }

    /* Tambahkan CSS untuk alert */
    .alert {
        margin: 20px auto;
        max-width: 800px;
        font-size: 16px;
        padding: 15px;
    }

    .alert i.fa-warning {
        margin-right: 10px;
        font-size: 20px;
    }

    .alert .btn {
        margin-left: 15px;
    }

    .alert strong {
        font-size: 16px;
        margin-right: 5px;
    }
    </style>

    <!-- Tambahkan script untuk alert -->
    <script>
    // Auto-hide alert setelah 10 detik
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);

    // Tampilkan kembali alert saat hover
    $('.alert').hover(function() {
        $(this).stop().fadeIn();
    });
    </script>

<?php
} else { // user biasa
?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Dashboard</h1>
            </div>
        </div>
        
        <div class="row">
            <!-- Konten dashboard user biasa -->
            <div class="col-lg-3 col-md-6">
                <!-- Panel Total Peminjaman -->
            </div>
            <div class="col-lg-3 col-md-6">
                <!-- Panel Rating Saya -->
            </div>
        </div>
    </div>
<?php
}
?>

<?php
// include "../template/footer.php";

?>