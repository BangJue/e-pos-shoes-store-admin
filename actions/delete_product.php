<?php
require_once "../config/koneksi.php";

if(isset($_GET['id'])) {

    $id = $_GET['id'];

    $query = mysqli_query($conn,"DELETE FROM produk WHERE id_produk = '$id'");

    if($query) {
        header("Location: ../views/produk.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }

}

?>
