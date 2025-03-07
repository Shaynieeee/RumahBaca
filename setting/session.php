<?php
   include('koneksi.php');
   
   // Cek apakah session sudah dimulai
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }
   
   $user_check = $_SESSION['login_user'];
   
   $ses_sql = mysqli_query($db,"select username from t_account where username = '$user_check' ");
   
   $row = mysqli_fetch_array($ses_sql,MYSQLI_ASSOC);
   
   $login_session = $row['username'];
   
   // Cek apakah user sudah login
   if(!isset($_SESSION['login_user'])){
      header("location: ../login.php");
      exit();
   }

   // Cek role untuk akses halaman
   function cek_role($allowed_roles) {
       if (!in_array($_SESSION['role'], $allowed_roles)) {
           header("location: ../login.php");
           exit();
       }
   }
?>