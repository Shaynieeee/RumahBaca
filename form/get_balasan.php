<?php
session_start();
require_once '../setting/koneksi.php';

$id_rating = $_POST['id_rating'];

$sql = "SELECT rb.*, a.username, DATE_FORMAT(rb.create_date, '%d-%m-%Y %H:%i') as tanggal,
        (SELECT COUNT(*) FROM t_rating_balasan_like WHERE id_balasan = rb.id_t_rating_balasan AND jenis = 'like') as jumlah_like,
        (SELECT COUNT(*) FROM t_rating_balasan_like WHERE id_balasan = rb.id_t_rating_balasan AND jenis = 'dislike') as jumlah_dislike
        FROM t_rating_balasan rb
        JOIN t_account a ON rb.create_by = a.username
        WHERE rb.id_rating = ?
        ORDER BY rb.create_date ASC";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_rating);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result)) {
    echo '<div class="balasan-item">';
    echo '<div class="balasan-header">';
    echo '<strong>'.$row['username'].'</strong>';
    echo '<small>'.$row['tanggal'].'</small>';
    echo '</div>';
    echo '<div class="balasan-content">'.$row['balasan'].'</div>';
    echo '<div class="balasan-actions">';
    echo '<span id="balasan-like-'.$row['id_t_rating_balasan'].'">'.$row['jumlah_like'].'</span>';
    echo '<button onclick="likeBalasan('.$row['id_t_rating_balasan'].',\'like\')" class="btn btn-sm btn-success"><i class="fa fa-thumbs-up"></i></button>';
    echo '<span id="balasan-dislike-'.$row['id_t_rating_balasan'].'">'.$row['jumlah_dislike'].'</span>';
    echo '<button onclick="likeBalasan('.$row['id_t_rating_balasan'].',\'dislike\')" class="btn btn-sm btn-danger"><i class="fa fa-thumbs-down"></i></button>';
    echo '</div>';
    echo '</div>';
}
?> 