<?php

$host = 'localhost';
$db = 'epos';
$pw = '';
$username = 'root';

$conn = mysqli_connect($host,$username,$pw,$db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

?>