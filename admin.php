<?php
session_start();
include "koneksi.php";

/**
 * Helper: escape untuk HTML output
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper: buat tag <img> yang aman
 */
function image_tag($platFolder, $subdir, $filename) {
    if (empty($filename)) {
        return "<img src='noimage.png' class='img-fluid rounded border' alt='no image'>";
    }
    $filename = basename($filename);
    $serverPath = __DIR__ . "/upload/{$platFolder}/{$subdir}/{$filename}";
    $platUrl = rawurlencode($platFolder);
    $fileUrl = rawurlencode($filename);
    $url = "upload/{$platUrl}/{$subdir}/{$fileUrl}";

    if (file_exists($serverPath)) {
        return "<img src='{$url}' class='img-fluid rounded border' alt='image' onerror=\"this.src='noimage.png'\">";
    } else {
        return "<img src='noimage.png' class='img-fluid rounded border' alt='no image'>";
    }
}

function safeVal($row, $key) {
    return isset($row[$key]) ? $row[$key] : null;
}

/**
 * Ambil notifikasi
 */
$notif = 0;
$countSql = "SELECT COUNT(*) AS total FROM cek_kendaraan";
if ($res = mysqli_query($conn, $countSql)) {
    $notifRow = mysqli_fetch_assoc($res);
    $notif = isset($notifRow['total']) ? (int)$notifRow['total'] : 0;
    mysqli_free_result($res);
}

/**
 * Proses search
 */
$where = "";
$cari = "";
if (!empty($_GET['cari'])) {
    $cari = trim($_GET['cari']);
    $cari_safe = mysqli_real_escape_string($conn, $cari);
    $where = "WHERE u.nip LIKE '%$cari_safe%' OR u.nama LIKE '%$cari_safe%' OR m.plat LIKE '%$cari_safe%' OR m.tipe_mobil LIKE '%$cari_safe%' OR ck.komentar LIKE '%$cari_safe%'";
}

/**
 * Ambil data
 */
$query = "SELECT ck.*,
                    u.nama AS nama_user,
                    u.nip AS nip_user,
                    m.plat AS plat_mobil,
                    m.tipe_mobil AS tipe_mobil
                    FROM cek_kendaraan ck
                    JOIN tabel_user u ON ck.user_id = u.id_user
                    JOIN tabel_mobil m ON ck.mobil_id = m.id_mobil
            $where
            ORDER BY id DESC";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Check Up Mobil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(145deg,
                #6a1b9a 0%,
                #3a6edc 25%,
                #ff8f00 50%,
                #d1d1d1 100%
            );
            background-attachment: fixed;
            background-size: cover;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: white !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .badge-notif {
            min-width: 40px;
            padding: 8px 12px !important;
            font-size: 1rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #ff5c5c, #e04343) !important;
            box-shadow: 0 4px 12px rgba(255, 92, 92, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .notif-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        /* Container */
        .container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        /* Header Section */
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.6s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header h4 {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
            margin: 0;
        }

        /* Search Form */
        .search-form input {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }

        .search-form input:focus {
            border-color: #3a6edc;
            box-shadow: 0 0 0 3px rgba(58, 110, 220, 0.1);
            outline: none;
        }

        .search-form button {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc);
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 27, 154, 0.4);
        }

        /* Card Table */
        .card-table {
            background: rgba(202, 202, 202, 1);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: #333;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(58, 110, 220, 0.05);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* Button */
        .btn-detail {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            background-size: 200% 200%;
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 27, 154, 0.4);
            color: white;
        }

        /* Modal - PENTING: Hilangkan z-index override */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            border: none;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 2rem;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6a1b9a, #3a6edc);
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #3a6edc, #ff8f00);
        }

        .modal-body .fw-bold {
            color: #3a6edc;
            margin-bottom: 0.5rem;
        }

        .modal-body hr {
            border-top: 2px solid #e0e0e0;
            margin: 1.5rem 0;
        }

        .modal-footer {
            border: none;
            padding: 1rem 2rem;
        }

        .modal-footer .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-footer .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Image in Modal */
        .img-fluid {
            max-height: 200px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .img-fluid:hover {
            transform: scale(1.05);
        }

        .small-muted {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
            font-size: 1.1rem;
        }

        .navbar-custom {
            background-color: #d9d9d9;  /* abu-abu */
            border-radius: 15px;        /* sudut tidak lancip */
        }


        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1rem;
            }

            .search-form {
                width: 100% !important;
                max-width: none !important;
            }

            .table {
                font-size: 0.875rem;
            }

            .modal-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom px-3">
    <div class="d-flex align-items-center">
        <img src="gambar/hpi.png" alt="Logo" style="height: 110px;">
        cabang medan
    </div>
    <span class="navbar-brand mb-0 h1 mx-auto">Admin Dashboard - Check Up Mobil</span>
    <div class="d-flex align-items-center gap-3">
        <span class="notif-label">Notifikasi</span>
        <span id="notifBadge" class="badge badge-notif"><?php echo $notif; ?></span>
    </div>
</nav>

<div class="container">
    <div class="page-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"> Data Check Up Mobil</h4>
        <form method="GET" class="d-flex search-form" style="max-width:400px;">
            <input type="text" name="cari" value="<?php echo e($cari); ?>" class="form-control me-2" placeholder="Cari NIP / Nama / Plat...">
            <button class="btn" type="submit">Cari</button>
        </form>
    </div>

    <div class="card card-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:12%">NIP</th>
                            <th style="width:18%">Nama</th>
                            <th style="width:12%">No Plat</th>
                            <th style="width:12%">Kilometer</th>
                            <th style="width:15%">Lokasi</th>
                            <th style="width:15%">Tanggal</th>
                            <th style="width:16%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                    $id = (int)$row['id'];
                                    $no_plat = $row['plat_mobil'];
                                    $modalId = "detailModal" . $id;
                                ?>
                                <tr>
                                    <td><?php echo e($row['nip_user']); ?></td>
                                    <td><?php echo e($row['nama_user']); ?></td>
                                    <td><strong><?php echo e($no_plat); ?></strong></td>
                                    <td><?php echo e($row['kilometer']); ?> km</td>
                                    <td><?php echo e($row['lokasi']); ?></td>
                                    <td><?php echo e($row['created_at']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-detail btn-sm" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                                             Selengkapnya
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <!-- <div>📭</div> -->
                                    <div>Tidak ada data check up yang tersedia.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals - Taruh di luar table -->
<?php
mysqli_data_seek($result, 0); // Reset pointer
while ($row = mysqli_fetch_assoc($result)):
    $id = (int)$row['id'];
    $no_plat = $row['plat_mobil'];
    $modalId = "detailModal" . $id;
?>
<div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> Detail Kendaraan - <?php echo e($no_plat); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Info Pegawai & Mobil -->
                <div class="mb-3">
                    <label class="fw-bold">NIP:</label>
                    <div><?php echo e($row['nip_user']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Nama:</label>
                    <div><?php echo e($row['nama_user']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Plat:</label>
                    <div><?php echo e($no_plat); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Tipe Mobil:</label>
                    <div><?php echo e($row['tipe_mobil']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Kilometer:</label>
                    <div><?php echo e($row['kilometer']); ?> km</div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Lokasi:</label>
                    <div><?php echo e($row['lokasi']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Tanggal:</label>
                    <div><?php echo e($row['created_at']); ?></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Keluhan:</label>
                    <div class="small-muted"><?php echo nl2br(e($row['komentar'])); ?></div>
                </div>

                <hr>
                <h6 class="fw-bold mb-3" style="color: #3a6edc;"> Foto Kendaraan</h6>

                <div class="row g-3">
                    <div class="col-6">
                        <p class="mb-2 small-muted">Odometer & Panel</p>
                        <?php echo image_tag($no_plat, 'odometer', safeVal($row, 'foto_bukti')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Tampak Depan</p>
                        <?php echo image_tag($no_plat, 'tampak_kendaraan', safeVal($row, 'depan')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Tampak Belakang</p>
                        <?php echo image_tag($no_plat, 'tampak_kendaraan', safeVal($row, 'belakang')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Tampak Kanan</p>
                        <?php echo image_tag($no_plat, 'tampak_kendaraan', safeVal($row, 'kanan')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Tampak Kiri</p>
                        <?php echo image_tag($no_plat, 'tampak_kendaraan', safeVal($row, 'kiri')); ?>
                    </div>
                </div>

                <hr>
                <h6 class="fw-bold mb-3" style="color: #3a6edc;"> Interior Kendaraan</h6>

                <div class="row g-3">
                    <div class="col-6">
                        <p class="mb-2 small-muted">Jok & Sabuk Pengaman</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'joksabuk')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">AC & Ventilasi</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'acventilasi')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Audio</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'panelaudio')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Lampu Kabin</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'lampukabin')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Kebersihan Interior</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'interior_bersih')); ?>
                    </div>
                    <div class="col-6">
                        <p class="mb-2 small-muted">Toolkit & Dongkrak</p>
                        <?php echo image_tag($no_plat, 'interior', safeVal($row, 'toolkitdongkrak')); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endwhile; ?>
<?php if ($result) mysqli_free_result($result); ?>

<audio id="notifAudio" src="notif.mp3" preload="auto"></audio>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let lastKomentar = "";
let lastTotal = <?php echo (int)$notif; ?>;

async function cekNotif() {
    try {
        const res = await fetch('notif.php', { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        if (!data) return;

        if (typeof data.total !== 'undefined') {
            const total = parseInt(data.total) || 0;
            if (total !== lastTotal) {
                document.getElementById('notifBadge').textContent = total;
                if (total > lastTotal) document.getElementById('notifAudio').play().catch(e=>{});
                lastTotal = total;
            }
        }

        if (data.komentar && data.komentar !== lastKomentar) {
            lastKomentar = data.komentar;
            document.getElementById('notifAudio').play();
            alert("Data baru: " + "Plat Mobil : " + data.plat_mobil + " Keluhan : " + data.komentar);
        }
    } catch (err) { console.log('Error notif:', err); }
}

setInterval(cekNotif, 3000);
cekNotif();
</script>
</body>
</html>