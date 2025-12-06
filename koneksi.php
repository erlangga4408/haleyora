<?php
// Coba ambil kredensial dari Environment Variables (Railway)
// Jika tidak ditemukan (misalnya saat testing di lokal), gunakan nilai default (localhost)

$host = getenv('MYSQL_HOST') ?: "localhost";
$user = getenv('MYSQL_USER') ?: "root";
$pass = getenv('MYSQL_PASSWORD') ?: ""; // Ganti dengan password lokal Anda jika ada
$dbname = getenv('MYSQL_DATABASE') ?: "haleyora"; // Ganti dengan nama database lokal Anda

// Untuk Railway, port default MySQL mungkin berbeda, sebaiknya masukkan juga:
// $port = getenv('MYSQL_PORT') ?: 3306; 

// Lakukan koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);
// Jika Anda memasukkan port, gunakan mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Koneksi berhasil
?>