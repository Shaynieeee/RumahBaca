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

// Cek ID buku
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: data_buku.php");
    exit();
}

// Ambil detail buku
$id_buku = (int) $_GET['id'];
$sql = "SELECT * FROM t_buku WHERE id_t_buku = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_buku);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result);

// Jika buku tidak ditemukan
if (!$buku) {
    header("location: data_buku.php");
    exit();
}

// Ambil data anggota yang login untuk cek akses download
$username = $_SESSION['login_user'];
$sql_anggota = "SELECT a.* FROM t_anggota a 
                JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
                WHERE acc.username = ?";
$stmt_anggota = mysqli_prepare($db, $sql_anggota);
mysqli_stmt_bind_param($stmt_anggota, "s", $username);
mysqli_stmt_execute($stmt_anggota);
$result_anggota = mysqli_stmt_get_result($stmt_anggota);
$anggota = mysqli_fetch_assoc($result_anggota);
$id_anggota = $anggota['id_t_anggota'];
$allow_download = $anggota['allow_download'] ?? 0;

include("header_anggota.php");
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <?php
                // Cari file gambar yang sesuai
                $gambar_id = $buku['gambar'] ?? '';
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
                    alt="<?php echo htmlspecialchars($buku['nama_buku']); ?>" style="object-fit: cover; height: 400px;">
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Detail Buku</h4>
                </div>
                <div class="card-body">
                    <!-- Tampilkan rating rata-rata -->
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">Rating Rata-rata</h5>
                            <?php
                            // Query untuk mengambil rata-rata rating dan total ulasan
                            $sql_avg = "SELECT 
                                            COALESCE(AVG(rating), 0) as avg_rating,
                                            COUNT(*) as total_rating
                                        FROM t_rating_buku 
                                        WHERE id_t_buku = ?";

                            $stmt_avg = mysqli_prepare($db, $sql_avg);
                            mysqli_stmt_bind_param($stmt_avg, "i", $id_buku);
                            mysqli_stmt_execute($stmt_avg);
                            $result_avg = mysqli_stmt_get_result($stmt_avg);
                            $rating_data = mysqli_fetch_assoc($result_avg);

                            $avg_rating = number_format($rating_data['avg_rating'], 1);
                            $total_rating = $rating_data['total_rating'];
                            ?>

                            <div class="display-4 text-warning mb-2"><?php echo $avg_rating; ?></div>
                            <div class="star-rating-display mb-2">
                                <?php
                                $full_stars = floor($avg_rating);
                                $half_star = $avg_rating - $full_stars >= 0.5;

                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $full_stars) {
                                        echo '<i class="fa fa-star fa-2x text-warning"></i>';
                                    } elseif ($i == $full_stars + 1 && $half_star) {
                                        echo '<i class="fa fa-star-half-o fa-2x text-warning"></i>';
                                    } else {
                                        echo '<i class="fa fa-star-o fa-2x text-warning"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="text-muted mb-0">
                                <?php
                                if ($total_rating > 0) {
                                    echo "Berdasarkan $total_rating ulasan";
                                } else {
                                    echo "Belum ada ulasan";
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <table class="table table-hover">
                        <tr>
                            <th width="200">Judul Buku</th>
                            <td><?php echo htmlspecialchars($buku['nama_buku']); ?></td>
                        </tr>
                        <tr>
                            <th>Penulis</th>
                            <td><?php echo htmlspecialchars($buku['penulis']); ?></td>
                        </tr>
                        <tr>
                            <th>Penerbit</th>
                            <td><?php echo htmlspecialchars($buku['penerbit']); ?></td>
                        </tr>
                        <tr>
                            <th>Tahun Terbit</th>
                            <td><?php echo htmlspecialchars($buku['tahun_terbit']); ?></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td><?php echo htmlspecialchars($buku['jenis']); ?></td>
                        </tr>
                        <tr>
                            <th>Sinopsis</th>
                            <td><?php echo nl2br(htmlspecialchars($buku['sinopsis'])); ?></td>
                        </tr>
                        <tr>
                            <th>Ketersediaan</th>
                            <td>
                                <?php if (!empty($buku['file_buku'])): ?>
                                    <?php if ($buku['stok'] > 0): ?>
                                        <div class="mb-2">
                                            <span class="badge badge-success">Tersedia Offline</span>
                                            <span class="badge badge-info ml-2">Tersedia Online</span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-book mr-1"></i>Stok: <?php echo $buku['stok']; ?> buku
                                        </small>
                                    <?php else: ?>
                                        <span class="badge badge-info">Tersedia Hanya Online</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($buku['stok'] > 0): ?>
                                        <div>
                                            <span class="badge badge-success">Tersedia Offline</span>
                                            <br>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-book mr-1"></i>Stok: <?php echo $buku['stok']; ?> buku
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="data_buku.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <?php if (!empty($buku['file_buku'])): ?>
                            <a href="baca-buku.php?id=<?php echo $buku['id_t_buku']; ?>" class="btn btn-primary">
                                <i class="fas fa-book-reader mr-2"></i>Baca Buku
                            </a>

                            <?php if ($allow_download == 1): ?>
                                <a href="download-buku.php?id=<?php echo $buku['id_t_buku']; ?>" class="btn btn-success">
                                    <i class="fas fa-download mr-2"></i>Download PDF
                                </a>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Setelah informasi buku -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Rating & Ulasan</h5>
                </div>
                <div class="card-body">
                    <!-- Tampilkan Ulasan -->
                    <div class="mt-4">
                        <?php
                        // Query yang benar sesuai struktur database
                        $sql_rating = "SELECT r.*, a.nama as nama 
                                       FROM t_rating_buku r 
                                       JOIN t_anggota a ON r.id_t_anggota = a.id_t_anggota 
                                       WHERE r.id_t_buku = ? 
                                       ORDER BY r.created_date DESC";
                        $stmt_rating = mysqli_prepare($db, $sql_rating);
                        mysqli_stmt_bind_param($stmt_rating, "i", $buku['id_t_buku']);
                        mysqli_stmt_execute($stmt_rating);
                        $result_rating = mysqli_stmt_get_result($stmt_rating);
                        ?>

                        <!-- Form Rating -->
                        <?php if (isset($_SESSION['login_user'])): ?>
                            <form action="proses_rating.php" method="POST" class="mb-4">
                                <input type="hidden" name="id_buku" value="<?php echo $buku['id_t_buku']; ?>">
                                <div class="form-group">
                                    <label class="d-block mb-">Berikan Rating</label>
                                    <div class="star-rating">
                                        <input type="radio" id="star5" name="rating" value="5" />
                                        <label for="star5">
                                            <i class="fa fa-star fa-2x"></i>
                                        </label>
                                        <input type="radio" id="star4" name="rating" value="4" />
                                        <label for="star4">
                                            <i class="fa fa-star fa-2x"></i>
                                        </label>
                                        <input type="radio" id="star3" name="rating" value="3" />
                                        <label for="star3">
                                            <i class="fa fa-star fa-2x"></i>
                                        </label>
                                        <input type="radio" id="star2" name="rating" value="2" />
                                        <label for="star2">
                                            <i class="fa fa-star fa-2x"></i>
                                        </label>
                                        <input type="radio" id="star1" name="rating" value="1" />
                                        <label for="star1">
                                            <i class="fa fa-star fa-2x"></i>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Tulis Ulasan Anda</label>
                                    <textarea name="ulasan" class="form-control" rows="3" required
                                        placeholder="Bagikan pendapat Anda tentang buku ini..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">
                                    <i class="fa fa-paper-plane"></i> Kirim Ulasan
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Tampilan ulasan yang sudah ada -->
                        <?php while ($rating = mysqli_fetch_assoc($result_rating)): ?>
                            <div class="review-item shadow-sm rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-primary">
                                            <i class="fa fa-user-circle"></i>
                                            <?php echo htmlspecialchars($rating['nama']); ?>
                                        </strong>
                                        <div class="star-rating-display mt-1">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating['rating']) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-clock-o"></i>
                                        <?php echo date('d M Y', strtotime($rating['created_date'])); ?>
                                    </small>
                                </div>
                                <p class="mt-2 mb-2"><?php echo nl2br(htmlspecialchars($rating['ulasan'])); ?></p>

                                <!-- Ganti bagian tombol like/dislike -->
                                <div class="d-flex align-items-center mt-2">
                                    <?php
                                    // Cek apakah user sudah like/dislike
                                    $sql_check = "SELECT jenis FROM t_rating_like 
                                                  WHERE id_rating = ? AND username = ?";
                                    $stmt_check = mysqli_prepare($db, $sql_check);
                                    mysqli_stmt_bind_param($stmt_check, "is", $rating['id_rating'], $_SESSION['login_user']);
                                    mysqli_stmt_execute($stmt_check);
                                    $user_action = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check));

                                    // Hitung jumlah like dan dislike
                                    $sql_count = "SELECT 
                                                    SUM(CASE WHEN jenis = 'like' THEN 1 ELSE 0 END) as likes,
                                                    SUM(CASE WHEN jenis = 'dislike' THEN 1 ELSE 0 END) as dislikes
                                                  FROM t_rating_like 
                                                  WHERE id_rating = ?";
                                    $stmt_count = mysqli_prepare($db, $sql_count);
                                    mysqli_stmt_bind_param($stmt_count, "i", $rating['id_rating']);
                                    mysqli_stmt_execute($stmt_count);
                                    $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count));
                                    ?>

                                    <!-- Tombol Like -->
                                    <button type="button"
                                        class="btn btn-sm btn-outline-success mr-2 <?php echo ($user_action && $user_action['jenis'] == 'like') ? 'active' : ''; ?>"
                                        data-action="like" data-rating="<?php echo $rating['id_rating']; ?>">
                                        <i class="fa fa-thumbs-up"></i>
                                        <span><?php echo $counts['likes'] ?? 0; ?></span>
                                    </button>

                                    <!-- Tombol Dislike -->
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger mr-2 <?php echo ($user_action && $user_action['jenis'] == 'dislike') ? 'active' : ''; ?>"
                                        data-action="dislike" data-rating="<?php echo $rating['id_rating']; ?>">
                                        <i class="fa fa-thumbs-down"></i>
                                        <span><?php echo $counts['dislikes'] ?? 0; ?></span>
                                    </button>
                                </div>

                                <!-- Form Balasan (awalnya tersembunyi) -->
                                <div class="mt-3 reply-form" style="display:none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <form class="reply-form" method="POST">
                                                <input type="hidden" name="id_rating"
                                                    value="<?php echo $rating['id_rating']; ?>">
                                                <div class="form-group">
                                                    <textarea name="balasan" class="form-control" rows="3"
                                                        placeholder="Tulis balasan Anda..." required></textarea>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-paper-plane"></i> Kirim Balasan
                                                    </button>
                                                    <button type="button" class="btn btn-light btn-sm cancel-reply">
                                                        <i class="fa fa-times"></i> Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Daftar balasan yang sudah ada -->
                                <div class="replies-list ml-4 mt-3">
                                    <?php
                                    $sql_replies = "SELECT b.*, a.nama 
                                                    FROM t_rating_balasan b 
                                                    JOIN t_anggota a ON b.create_by = a.id_t_anggota 
                                                    WHERE b.id_rating = ? 
                                                    ORDER BY b.create_date ASC";
                                    $stmt_replies = mysqli_prepare($db, $sql_replies);
                                    mysqli_stmt_bind_param($stmt_replies, "i", $rating['id_rating']);
                                    mysqli_stmt_execute($stmt_replies);
                                    $replies = mysqli_stmt_get_result($stmt_replies);

                                    while ($reply = mysqli_fetch_assoc($replies)):
                                        ?>
                                        <div class="reply-item border-left pl-3 py-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="text-primary">
                                                    <i class="fa fa-user-circle"></i>
                                                    <?php echo htmlspecialchars($reply['nama']); ?>
                                                </strong>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock"></i>
                                                    <?php echo date('d M Y H:i', strtotime($reply['create_date'])); ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($reply['balasan'])); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan setelah informasi buku -->
    <div class="card mb-3">
        <div class="card-body">
            <?php
            // Cek apakah sedang membaca
            $sql_check = "SELECT id_riwayat, waktu_mulai 
                         FROM t_riwayat_baca 
                         WHERE id_t_anggota = ? 
                         AND id_t_buku = ? 
                         AND tanggal_baca = CURRENT_DATE()
                         AND waktu_selesai IS NULL";
            $stmt = mysqli_prepare($db, $sql_check);
            mysqli_stmt_bind_param($stmt, "ii", $id_anggota, $id_buku);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $sedang_baca = mysqli_fetch_assoc($result);

            if ($sedang_baca) {
                // Jika sedang membaca, tampilkan tombol Selesai
                ?>
                <form action="proses_selesai_baca.php" method="post">
                    <input type="hidden" name="id_riwayat" value="<?php echo $sedang_baca['id_riwayat']; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Selesai Baca
                    </button>
                    <small class="text-muted d-block mt-2">
                        Mulai baca: <?php echo $sedang_baca['waktu_mulai']; ?>
                    </small>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background-color: #f8f9fa;
    }

    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        cursor: pointer;
        width: 28px;
        height: 28px;
        margin-right: 5px;
        float: right;
        position: relative;
    }

    .star-rating label:before {
        content: "\f005";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        color: #ddd;
        font-size: 24px;
        position: absolute;
        top: 0;
        left: 0;
    }

    .star-rating input:checked~label:before,
    .star-rating label:hover:before,
    .star-rating label:hover~label:before {
        color: #ffd700;
    }

    .star-rating-display .fas.fa-star,
    .star-rating-display .far.fa-star {
        color: #ffd700;
        font-size: 18px;
        margin-right: 2px;
    }

    .star-rating-display {
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .star-rating-display i {
        font-size: 24px;
    }

    .display-4 {
        font-size: 3rem;
        font-weight: bold;
        color: #ffd700;
    }

    .star-rating-display {
        font-size: 24px;
        margin: 10px 0;
    }

    .ulasan-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .reply-item {
        border-left: 3px solid #007bff !important;
        background-color: #f8f9fa;
    }

    .reply-form .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Pastikan Font Awesome sudah di-include di header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- Tambahkan script JavaScript -->
<script>
    $(document).ready(function () {
        $('.btn-outline-success, .btn-outline-danger').click(function () {
            var button = $(this);
            var action = button.data('action');
            var ratingId = button.data('rating');

            $.ajax({
                url: '../proses_like_rating.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_rating: ratingId,
                    jenis: action
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Update counts
                        button.closest('.d-flex').find('.btn-outline-success span').text(response.likes);
                        button.closest('.d-flex').find('.btn-outline-danger span').text(response.dislikes);

                        // Update active state
                        button.closest('.d-flex').find('button').removeClass('active');
                        if (response.user_action) {
                            button.addClass('active');
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses like/dislike');
                }
            });
        });

        // Toggle form balasan
        $('.btn-outline-primary').click(function () {
            $(this).closest('.review-item').find('.reply-form').slideToggle();
        });

        // Batalkan balasan
        $('.cancel-reply').click(function () {
            $(this).closest('.reply-form').slideUp();
        });

        // Submit balasan
        $('.reply-form form').submit(function (e) {
            e.preventDefault();
            var form = $(this);
            var replyList = form.closest('.review-item').find('.replies-list');

            $.ajax({
                url: '../proses_balas_rating.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Tambahkan balasan baru ke daftar
                        var newReply = `
                        <div class="reply-item border-left pl-3 py-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-primary">
                                    <i class="fa fa-user-circle"></i> 
                                    ${response.nama}
                                </strong>
                                <small class="text-muted">
                                    <i class="fa fa-clock"></i> 
                                    ${response.tanggal}
                                </small>
                            </div>
                            <p class="mb-0 mt-2">${response.balasan}</p>
                        </div>
                    `;
                        replyList.append(newReply);

                        // Reset & sembunyikan form
                        form[0].reset();
                        form.closest('.reply-form').slideUp();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim balasan');
                }
            });
        });
    });
</script>

<?php include("footer.php"); ?>