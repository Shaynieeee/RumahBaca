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

if ($row['role']==1 || $row['role']==2){
?>
<div id="page-wrapper" style="margin-bottom: 10px">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Dashboard</h1>
			</div>
		</div>

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
				<div class="panel panel-red">
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
				<div class="panel panel-green">
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
				<div class="panel panel-yellow">
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
	</div>
</div>
<?php
	}else{
?>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header">Dashboard</h1>
		</div>
	</div>
	
	<div class="row">
		<div class="col-lg-3 col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="fa fa-book fa-5x"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge"><?php echo $row_jb['JUMLAH'];?></div>
							<div>Total Peminjaman</div>
						</div>
					</div>
				</div>
				<a href="history_peminjaman.php?id=<?php echo $row_jb['ID'];?>">
					<div class="panel-footer">
						<span class="pull-left">Lihat Detail</span>
						<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
						<div class="clearfix"></div>
					</div>
				</a>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-yellow">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="fa fa-star fa-5x"></i>
						</div>
						<div class="col-xs-9 text-right">
							<?php
							// Query untuk menghitung rating user yang sudah diperbaiki
							$sql_my_rating = "SELECT COUNT(*) as JUM 
											FROM t_rating_buku r 
											JOIN t_anggota a ON r.id_t_anggota = a.id_t_anggota 
											JOIN t_account ac ON a.id_t_account = ac.id_t_account 
											WHERE ac.username = '$user_check'";
							$result_my_rating = mysqli_query($db, $sql_my_rating);
							$row_my_rating = mysqli_fetch_array($result_my_rating, MYSQLI_ASSOC);
							?>
							<div class="huge"><?php echo $row_my_rating['JUM'];?></div>
							<div>Rating Saya</div>
						</div>
					</div>
				</div>
				<a href="my_ratings.php">
					<div class="panel-footer">
						<span class="pull-left">Lihat Detail</span>
						<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
						<div class="clearfix"></div>
					</div>
				</a>
			</div>
		</div>
	</div>
</div>
<?php
	}
?>

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
</style>

<?php 
// include "../template/footer.php";

?>