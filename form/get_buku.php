<?php
require_once '../setting/koneksi.php';
header('Content-Type: application/json');

if(isset($_POST['search'])) {
    $search = mysqli_real_escape_string($db, $_POST['search']);
    
    $query = "SELECT id_t_buku, nama_buku, penulis, stok 
              FROM t_buku 
              WHERE LOWER(nama_buku) LIKE LOWER('%$search%') 
              AND stok > 0 
              ORDER BY nama_buku 
              LIMIT 10";
              
    $result = mysqli_query($db, $query);
    
    $response = array();
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $response[] = array(
                "id" => $row['id_t_buku'],
                "value" => $row['nama_buku'],
                "label" => $row['nama_buku'] . " - " . $row['penulis'] . " (Stok: " . $row['stok'] . ")"
            );
        }
    }
    
    echo json_encode($response);
    exit;
}

echo json_encode(array());
?> 