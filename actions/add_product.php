<?php
require_once "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = $_POST['nama_produk'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // Upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];

    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $path_gambar = $upload_dir . $gambar;
    move_uploaded_file($tmp, $path_gambar);

    $query = "INSERT INTO produk (nama_produk, kategori, harga, stok, gambar) 
              VALUES ('$nama', '$kategori', '$harga', '$stok', '$gambar')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../views/produk.php?success=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
