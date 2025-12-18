<?php
$servername = "localhost";
$database = "dw_adventurework_fp";
$username = "root";
$password = "";

// membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $database);
// mengecek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
//echo "Koneksi berhasil";

//mysqli_close($conn);

?>