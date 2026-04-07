<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM akun WHERE username='$username' LIMIT 1";
    $run = mysqli_query($conn, $query);

    if ($run && mysqli_num_rows($run) === 1) {
        $data = mysqli_fetch_assoc($run);

        // Password tidak hash → langsung cocokkan string
        if ($password === $data['password']) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama'] = $data['nama_lengkap'];

            header("Location: ../views/index.php");
            exit;
        } else {
            echo
           " <script>
            alert('Password Salah!');
            window.location.href = '../login.php';
            </script>"; 
        }
    } else {
        echo
           " <script>
            alert('Akun Salah!');
            window.location.href = '../login.php';
            </script>"; 
    }
}
?>
