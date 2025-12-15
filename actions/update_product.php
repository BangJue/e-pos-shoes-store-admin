<?php
require_once "../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST['id_produk'];
    $nama = $_POST['nama_produk'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $gambar_lama = $_POST['gambar_lama'];

    // Cek apakah user upload gambar baru
    if (!empty($_FILES['gambar']['name'])) {
        $gambar_baru = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];

        $folder = "../uploads/";
        $path_gambar = $folder . $gambar_baru;

        move_uploaded_file($tmp, $path_gambar);
    } else {
        $gambar_baru = $gambar_lama;
    }

    $query = "UPDATE produk SET 
                nama_produk='$nama',
                kategori='$kategori',
                harga='$harga',
                stok='$stok',
                gambar='$gambar_baru'
              WHERE id_produk='$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../views/produk.php?update=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
