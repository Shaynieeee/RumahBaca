<?php
include("header.php");

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
?>

<head>
    <!-- Bootstrap CSS -->
    <link href="../template/css/bootstrap.min.css" rel="stylesheet">
</head>

<div id="page-wrapper" style="padding-left: 40px;">
    <div class="container-fluid">

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Data Rating & Ulasan</h1>
        </div>
    </div>

    <div class="row" style="margin-top:-80px;">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Daftar Rating & Ulasan
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <!-- Tambahkan style untuk tabel -->
                        <style>
                        .table > thead > tr > th {
                            vertical-align: middle;
                            text-align: center;
                            padding: 12px;
                        }

                        .table > tbody > tr > td {
                            vertical-align: middle;
                            padding: 12px;
                        }

                        .btn-group-action {
                            display: flex;
                            gap: 5px;
                            justify-content: center;
                        }

                        .like-dislike-count {
                            text-align: center;
                        }

                        .like-dislike-count i {
                            margin: 0 5px;
                        }

                        .rating-stars {
                            color: #ffc107;
                            white-space: nowrap;
                        }

                        .balasan-container {
                            margin-top: 10px;
                            padding: 10px;
                            background: #f8f9fa;
                            border-radius: 5px;
                        }

                        .balasan-item {
                            padding: 8px;
                            margin-bottom: 8px;
                            border-bottom: 1px solid #dee2e6;
                        }

                        .balasan-header {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 5px;
                        }

                        .balasan-actions {
                            display: flex;
                            gap: 5px;
                        }

                        .balasan-content {
                            margin-left: 20px;
                        }

                        .nested-balasan {
                            margin-left: 20px;
                            border-left: 2px solid #dee2e6;
                            padding-left: 10px;
                        }
                        </style>

                        <!-- Struktur tabel yang diperbaiki -->
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Judul Buku</th>
                                    <th width="15%">Nama Anggota</th>
                                    <th width="10%">Rating</th>
                                    <th width="20%">Ulasan</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="10%">Like/Dislike</th>
                                    <!-- <th width="10%">Aksi</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT r.*, b.nama_buku, a.nama, 
                                              DATE_FORMAT(r.created_date, '%d-%m-%Y %H:%i') as tanggal,
                                              (SELECT COUNT(*) FROM t_rating_like WHERE id_rating = r.id_rating AND jenis = 'like') as jumlah_like,
                                              (SELECT COUNT(*) FROM t_rating_like WHERE id_rating = r.id_rating AND jenis = 'dislike') as jumlah_dislike
                                       FROM t_rating_buku r
                                       JOIN t_buku b ON r.id_t_buku = b.id_t_buku
                                       JOIN t_anggota a ON r.id_t_anggota = a.id_t_anggota
                                       ORDER BY r.created_date DESC";
                                $result = mysqli_query($db, $sql);
                                
                                $no = 1;
                                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="text-center">
                                        <div class="rating-stars">
                                        <?php
                                        for($i = 1; $i <= 5; $i++) {
                                            if($i <= $row['rating']) {
                                                echo '<i class="fa fa-star"></i>';
                                            } else {
                                                echo '<i class="fa fa-star-o"></i>';
                                            }
                                        }
                                        ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['ulasan']); ?></td>
                                    <td class="text-center"><?php echo $row['tanggal']; ?></td>
                                    <td class="like-dislike-count">
                                        <span id="like-count-<?php echo $row['id_rating']; ?>">
                                            <i class="fa fa-thumbs-up text-success"></i> <?php echo $row['jumlah_like']; ?>
                                        </span>
                                        <span id="dislike-count-<?php echo $row['id_rating']; ?>" class="ml-2">
                                            <i class="fa fa-thumbs-down text-danger"></i> <?php echo $row['jumlah_dislike']; ?>
                                        </span>
                                    </td>
                                    <!-- <td>
                                        <div class="btn-group-action">
                                            <button onclick="likeRating(<?php echo $row['id_rating']; ?>, 'like')" class="btn btn-sm btn-success" title="Like">
                                                <i class="fa fa-thumbs-up"></i>
                                            </button>
                                            <button onclick="likeRating(<?php echo $row['id_rating']; ?>, 'dislike')" class="btn btn-sm btn-danger" title="Dislike">
                                                <i class="fa fa-thumbs-down"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary" 
                                                    onclick="balasUlasan(<?php echo $row['id_rating']; ?>)" 
                                                    title="Balas">
                                                <i class="fa fa-reply"></i>
                                            </button>
                                        </div> -->
                                        
                                        <!-- Container untuk balasan -->
                                        <div id="balasan-<?php echo $row['id_rating']; ?>" class="balasan-container mt-2" style="display:none;">
                                            <!-- Balasan akan dimuat di sini -->
                                            <div class="balasan-list"></div>
                                            
                                            <!-- Form untuk menambah balasan -->
                                            <div class="input-group mt-2">
                                                <input type="text" class="form-control form-control-sm balasan-input" placeholder="Tulis balasan...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-sm btn-primary" onclick="tambahBalasan(<?php echo $row['id_rating']; ?>)">
                                                        <i class="fa fa-paper-plane"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Balas Ulasan -->
    <div class="modal" id="modalBalas" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Balas Ulasan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formBalasan">
                        <input type="hidden" id="id_rating" name="id_rating">
                        <div class="form-group">
                            <label for="balasan">Balasan Admin/Staff:</label>
                            <textarea class="form-control" id="balasan" name="balasan" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="simpanBalasan()">Kirim Balasan</button>
                </div>
            </div>
        </div>
    </div>
                                    </div>
</div>

<!-- Hanya include file JS yang diperlukan -->
<script src="../template/js/jquery-3.7.0.min.js"></script>
<script src="../template/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Document ready');
});

function balasUlasan(id_rating) {
    console.log('ID Rating:', id_rating);
    
    if (!id_rating) {
        console.error('ID Rating tidak valid');
        return;
    }
    
    // Set nilai ke input hidden
    $('#id_rating').val(id_rating);
    
    // Reset textarea
    $('#balasan').val('');
    
    // Tampilkan modal
    $('#modalBalas').modal({
        show: true,
        backdrop: 'static',
        keyboard: false
    });
}

function simpanBalasan() {
    var id_rating = $('#id_rating').val();
    var balasan = $('#balasan').val();
    
    if (!balasan) {
        alert('Silakan isi balasan terlebih dahulu');
        return;
    }
    
    $.ajax({
        url: 'proses_balas_ulasan.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_rating: id_rating,
            balasan: balasan,
            is_admin: true
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                $('#modalBalas').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
            alert('Terjadi kesalahan: ' + error);
        }
    });
}
</script>

<?php 
// include("footer.php"); 
?> 