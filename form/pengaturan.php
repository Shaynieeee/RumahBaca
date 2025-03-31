<?php
// Pastikan ini di paling atas file, sebelum ada output apapun
session_start();
require_once('../setting/koneksi.php');

// Cek role user
$user_check = $_SESSION['login_user'];
$sql_role = "SELECT id_p_role as role FROM t_account WHERE username = '$user_check'";
$result_role = mysqli_query($db, $sql_role);
$row_role = mysqli_fetch_array($result_role, MYSQLI_ASSOC);
$user_role = $row_role['role'];

// Hanya admin dan staff yang bisa akses
if ($user_role != 1 && $user_role != 2) {
    header("location: dashboard.php");
    exit();
}

// Inisialisasi variabel pesan
$success_message = "";
$error_message = "";

// Tambahkan ini di awal file setelah koneksi database
$sql_create_table = "CREATE TABLE IF NOT EXISTS `t_pengaturan_denda` (
    `id_pengaturan` int(11) NOT NULL AUTO_INCREMENT,
    `jenis_denda` varchar(50) NOT NULL,
    `nilai_denda` decimal(10,2) NOT NULL,
    `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `create_by` varchar(50) NOT NULL,
    `update_by` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id_pengaturan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

if(mysqli_query($db, $sql_create_table)) {
    // Insert data default jika tabel kosong
    $sql_check = "SELECT COUNT(*) as count FROM t_pengaturan_denda";
    $result_check = mysqli_query($db, $sql_check);
    $row_check = mysqli_fetch_assoc($result_check);
    
    if($row_check['count'] == 0) {
        $default_values = [
            ['terlambat', 2000],
            ['rusak', 30],
            ['hilang', 100]
        ];
        
        foreach($default_values as $value) {
            $sql_insert = "INSERT INTO t_pengaturan_denda (jenis_denda, nilai_denda, create_by) 
                          VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            mysqli_stmt_bind_param($stmt, "sds", $value[0], $value[1], $_SESSION['login_user']);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Fungsi untuk mengecek apakah tabel ada
function tableExists($db, $table) {
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");
    return mysqli_num_rows($result) > 0;
}

// Ambil data batas baca jika tabel ada
$batas = [];
if(tableExists($db, 't_batas_baca')) {
    $sql = "SELECT * FROM t_batas_baca";
    $result = mysqli_query($db, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $batas[$row['id_batas']] = $row;
    }
}

// Ambil data kategori jika tabel ada
$kategori = [];
if(tableExists($db, 't_kategori')) {
    $sql = "SELECT * FROM t_kategori";
    $result = mysqli_query($db, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $kategori[$row['id_kategori']] = $row;
    }
}

// Ambil data akses download jika tabel ada
$akses = [];
if(tableExists($db, 't_akses_download')) {
    $sql = "SELECT * FROM t_akses_download";
    $result = mysqli_query($db, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $akses[$row['id_akses']] = $row;
    }
}

// Ambil pengaturan denda jika tabel ada
$pengaturan_denda = [];
if(tableExists($db, 't_pengaturan_denda')) {
    $sql_denda = "SELECT * FROM t_pengaturan_denda";
    $result_denda = mysqli_query($db, $sql_denda);
    while($row = mysqli_fetch_assoc($result_denda)) {
        $pengaturan_denda[$row['jenis_denda']] = $row['nilai_denda'];
    }
}

// Proses form submission untuk pengaturan denda
if(isset($_POST['update_pengaturan_denda'])) {
    $denda_terlambat = $_POST['denda_terlambat'];
    $denda_rusak = $_POST['denda_rusak'];
    $denda_hilang = $_POST['denda_hilang'];
    
    $update_values = [
        ['terlambat', $denda_terlambat],
        ['rusak', $denda_rusak],
        ['hilang', $denda_hilang]
    ];
    
    $success = true;
    foreach($update_values as $value) {
        $sql_update = "UPDATE t_pengaturan_denda 
                      SET nilai_denda = ?, 
                          update_by = ?,
                          update_date = CURRENT_TIMESTAMP
                      WHERE jenis_denda = ?";
        $stmt = mysqli_prepare($db, $sql_update);
        mysqli_stmt_bind_param($stmt, "dss", $value[1], $_SESSION['login_user'], $value[0]);
        
        if(!mysqli_stmt_execute($stmt)) {
            $success = false;
            break;
        }
    }
    
    if($success) {
        $_SESSION['success_message'] = "Pengaturan denda berhasil diperbarui";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui pengaturan denda";
    }
    
    // Redirect sebelum ada output apapun
    header("Location: pengaturan.php");
    exit();
}

// Proses form submission untuk batas baca (kode yang sudah ada)
if(isset($_POST['update_batas'])) {
    // ... kode update batas baca yang sudah ada ...
}

// Proses form submission untuk kategori (kode yang sudah ada)
if(isset($_POST['update_kategori'])) {
    // ... kode update kategori yang sudah ada ...
}

// Proses form submission untuk akses download (kode yang sudah ada)
if(isset($_POST['update_akses'])) {
    // ... kode update akses download yang sudah ada ...
}

// Ambil data kategori untuk ditampilkan
$sql_categories = "SELECT * FROM t_kategori_buku ORDER BY nama_kategori";
$result_categories = mysqli_query($db, $sql_categories);

// Ambil data anggota untuk pengaturan akses download
$sql_anggota = "SELECT id_t_anggota, no_anggota, nama, allow_download FROM t_anggota ORDER BY nama";
$result_anggota = mysqli_query($db, $sql_anggota);

// Ambil pengaturan batas baca default (ambil dari buku pertama sebagai referensi)
$sql_batas_baca = "SELECT batas_baca_guest FROM t_buku LIMIT 1";
$result_batas_baca = mysqli_query($db, $sql_batas_baca);
$batas_baca_default = 5; // Default jika belum ada buku
if(mysqli_num_rows($result_batas_baca) > 0) {
    $row_batas_baca = mysqli_fetch_assoc($result_batas_baca);
    $batas_baca_default = $row_batas_baca['batas_baca_guest'];
}

// Cek dan buat tabel t_kategori_buku jika belum ada
$sql_check_table = "SHOW TABLES LIKE 't_kategori_buku'";
$result_check_table = mysqli_query($db, $sql_check_table);
if(mysqli_num_rows($result_check_table) == 0) {
    // Tabel belum ada, buat tabel baru
    $sql_create_table = "CREATE TABLE t_kategori_buku (
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
    if($result_existing && mysqli_num_rows($result_existing) > 0) {
        while($row = mysqli_fetch_assoc($result_existing)) {
            $kategori = $row['jenis'];
            $sql_insert = "INSERT INTO t_kategori_buku (nama_kategori, create_by) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            mysqli_stmt_bind_param($stmt, "ss", $kategori, $user_check);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // Tambahkan beberapa kategori default jika tidak ada kategori yang sudah ada
        $default_categories = ['Umum', 'Novel', 'Pendidikan', 'Teknologi', 'Agama'];
        foreach($default_categories as $category) {
            $sql_insert = "INSERT INTO t_kategori_buku (nama_kategori, create_by) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $sql_insert);
            mysqli_stmt_bind_param($stmt, "ss", $category, $user_check);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Include header setelah semua proses PHP
include('header.php');
?>

<!-- Tampilkan pesan sukses/error jika ada -->
<?php if(isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?php 
        echo $_SESSION['success_message']; 
        unset($_SESSION['success_message']);
        ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo $_SESSION['error_message']; 
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"> </h1>
        </div>
    <div>
    
    <div class="row">
        <div class="col-lg-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#batas-baca" aria-controls="batas-baca" role="tab" data-toggle="tab">Batas Baca</a></li>
                <li role="presentation"><a href="#kategori" aria-controls="kategori" role="tab" data-toggle="tab">Kategori Buku</a></li>
                <li role="presentation"><a href="#akses-download" aria-controls="akses-download" role="tab" data-toggle="tab">Akses Download</a></li>
                <li role="presentation"><a href="#carousel" aria-controls="carousel" role="tab" data-toggle="tab">Carousel</a></li>
                <li role="presentation"><a href="#denda" aria-controls="denda" role="tab" data-toggle="tab">Pengaturan Denda</a></li>
            </ul>
            
            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Tab Batas Baca -->
                <div role="tabpanel" class="tab-pane active" id="batas-baca">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Pengaturan Batas Baca Default</h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label>Batas Halaman untuk Guest</label>
                                    <div class="input-group">
                                        <input type="number" name="batas_baca_default" class="form-control" required min="1" value="<?php echo $batas_baca_default; ?>">
                                        <div class="input-group-addon">halaman</div>
                                    </div>
                                    <p class="help-block">Nilai ini akan diterapkan ke semua buku. Minimum 1 halaman.</p>
                                </div>
                                <button type="submit" name="update_batas_baca" class="btn btn-primary">Perbarui Semua Buku</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Kategori Buku -->
                <div role="tabpanel" class="tab-pane" id="kategori">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Manajemen Kategori Buku</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label>Tambah Kategori Baru</label>
                                            <input type="text" name="category_name" class="form-control" required>
                                        </div>
                                        <button type="submit" name="add_category" class="btn btn-success">Tambah Kategori</button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <h4>Daftar Kategori</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Kategori</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $no = 1;
                                                while($row = mysqli_fetch_assoc($result_categories)): 
                                                ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                                    <td>
                                                        <form method="post" action="" style="display:inline;">
                                                            <input type="hidden" name="category_id" value="<?php echo $row['id_t_kategori']; ?>">
                                                            <button type="submit" name="delete_category" class="btn btn-danger btn-xs" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                                <?php if(mysqli_num_rows($result_categories) == 0): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Belum ada kategori</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Akses Download -->
                <div role="tabpanel" class="tab-pane" id="akses-download">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Pengaturan Akses Download Buku</h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table-anggota">
                                    <thead>
                                        <tr style="background: #0067b0; color:#fff;">
                                            <th>No</th>
                                            <th>No Anggota</th>
                                            <th>Nama</th>
                                            <th>Akses Download</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result_anggota)): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['no_anggota']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td>
                                                <?php if($row['allow_download'] == 1): ?>
                                                    <span class="label label-success">Diizinkan</span>
                                                <?php else: ?>
                                                    <span class="label label-danger">Tidak Diizinkan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="" style="display:inline;">
                                                    <input type="hidden" name="anggota_id" value="<?php echo $row['id_t_anggota']; ?>">
                                                    <input type="hidden" name="allow_download" value="<?php echo $row['allow_download'] == 1 ? 0 : 1; ?>">
                                                    <button type="submit" name="toggle_download_access" class="btn <?php echo $row['allow_download'] == 1 ? 'btn-danger' : 'btn-success'; ?> btn-xs">
                                                        <?php echo $row['allow_download'] == 1 ? '<i class="fa fa-ban"></i> Cabut Akses' : '<i class="fa fa-check"></i> Berikan Akses'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php if(mysqli_num_rows($result_anggota) == 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada anggota</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Carousel -->
                <div role="tabpanel" class="tab-pane" id="carousel">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Pengaturan Gambar Carousel</h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="row">
                                    <?php for($i = 1; $i <= 3; $i++): ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Gambar Carousel <?php echo $i; ?></label>
                                            <?php 
                                            $carousel_images = glob("../image/gambar$i.*");
                                            if(!empty($carousel_images)): 
                                            ?>
                                                <div class="thumbnail">
                                                    <img src="<?php echo $carousel_images[0]; ?>" alt="Carousel <?php echo $i; ?>" style="max-height: 200px;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="carousel_image_<?php echo $i; ?>" class="form-control" accept="image/*">
                                            <p class="help-block">Format: JPG, JPEG, PNG. Ukuran yang disarankan: 1200x400px</p>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <button type="submit" name="update_carousel" class="btn btn-primary">Perbarui Gambar Carousel</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Denda -->
                <div role="tabpanel" class="tab-pane" id="denda">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Pengaturan Denda</h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label>Denda Keterlambatan per Hari</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">Rp</span>
                                        <input type="number" name="denda_terlambat" class="form-control" value="<?php echo isset($pengaturan_denda['terlambat']) ? $pengaturan_denda['terlambat'] : 2000; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Denda Buku Rusak</label>
                                    <div class="input-group">
                                        <input type="number" name="denda_rusak" class="form-control" value="<?php echo isset($pengaturan_denda['rusak']) ? $pengaturan_denda['rusak'] : 30; ?>" required>
                                        <span class="input-group-addon">% dari harga buku</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Denda Buku Hilang</label>
                                    <div class="input-group">
                                        <input type="number" name="denda_hilang" class="form-control" value="<?php echo isset($pengaturan_denda['hilang']) ? $pengaturan_denda['hilang'] : 100; ?>" required>
                                        <span class="input-group-addon">% dari harga buku</span>
                                    </div>
                                </div>

                                <button type="submit" name="update_pengaturan_denda" class="btn btn-primary">Simpan Pengaturan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTables untuk tabel anggota
    $('#table-anggota').DataTable({
        responsive: true,
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data yang tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
    
    // Aktifkan tab berdasarkan hash URL
    var hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    // Ubah hash URL saat tab berubah
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
});
</script>


