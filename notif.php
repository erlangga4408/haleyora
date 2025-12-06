<?php
include "koneksi.php";

$sql = "SELECT ck.komentar,
                    m.plat AS plat_mobil
                    FROM cek_kendaraan ck
                    JOIN tabel_user u ON ck.user_id = u.id_user
                    JOIN tabel_mobil m ON ck.mobil_id = m.id_mobil
            ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

echo json_encode($data);
