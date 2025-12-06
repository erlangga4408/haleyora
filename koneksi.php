<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname   = "haleyora";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>