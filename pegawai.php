<?php
session_start();
include "koneksi.php";

// Cek apakah Nip & plat tersedia di session
if (!isset($_SESSION['nip']) || !isset($_SESSION['plat'])) {
    header("Location: login.php");
    exit;
}

$nip  = $_SESSION['nip'];
$plat = $_SESSION['plat'];

// Ambil data nip → dari table_user
$qNip = mysqli_query($conn, "SELECT * FROM tabel_user WHERE nip = '$nip' LIMIT 1");
$dataNip = mysqli_fetch_assoc($qNip);

// Ambil data MOBIL → dari table_mobil
$qMobil = mysqli_query($conn, "SELECT * FROM tabel_mobil WHERE plat = '$plat' LIMIT 1");
$dataMobil = mysqli_fetch_assoc($qMobil);

// Fungsi buat folder
function buatFolder($path) {
    if (!is_dir($path)) mkdir($path, 0777, true);
}

// Fungsi upload gambar
function upload_gambar($input_name, $folder, $rename) {
    if (!isset($_FILES[$input_name])) return "";

    $file = $_FILES[$input_name];

    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return "";
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "";
    }

    if (!is_dir($folder)) {
        if (!mkdir($folder, 0777, true)) {
            return "";
        }
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $namaAsli = pathinfo($file['name'], PATHINFO_FILENAME);
    $namaAsli = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaAsli);
    $namaBaru = $rename . '-' . $namaAsli . '.' . $ext;

    if (move_uploaded_file($file['tmp_name'], $folder . $namaBaru)) {
        return $namaBaru;
    }

    return "";
}

// Jika submit
if (isset($_POST['submit'])) {

    // Validasi dan format kilometer
    $kilometer_input = isset($_POST['kilometer']) ? trim($_POST['kilometer']) : '';
    
    // Validasi hanya angka
    if (!preg_match('/^[0-9]+$/', $kilometer_input)) {
        echo "<script>alert('Kilometer harus berisi angka saja!');</script>";
    } else {
        // Format dengan prefix 0 menjadi 6 karakter
        $kilometer = str_pad($kilometer_input, 6, '0', STR_PAD_LEFT);
        $kilometer = mysqli_real_escape_string($conn, $kilometer);
    }
    
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
    $tanggal = date("Y-m-d");

    // Folder upload sesuai PLAT
    $folderPlat     = "upload/" . $plat . "/";
    $folderOdometer = $folderPlat . "odometer/";
    $folderTampak   = $folderPlat . "tampak_kendaraan/";
    $folderInterior = $folderPlat . "interior/";

    buatFolder($folderOdometer);
    buatFolder($folderTampak);
    buatFolder($folderInterior);

    // Upload
    $foto_bukti = upload_gambar("bukti_kilometer", $folderOdometer, $tanggal);
    $depan      = upload_gambar("tampak_depan", $folderTampak, "depan-$tanggal");
    $belakang   = upload_gambar("tampak_belakang", $folderTampak, "belakang-$tanggal");
    $kanan      = upload_gambar("tampak_kanan", $folderTampak, "kanan-$tanggal");
    $kiri       = upload_gambar("tampak_kiri", $folderTampak, "kiri-$tanggal");

    // INTERIOR
    $joksabuk        = upload_gambar("interior_jok_sabuk", $folderInterior, "joksabuk-$tanggal");
    $acventilasi     = upload_gambar("interior_ac_ventilasi", $folderInterior, "acventilasi-$tanggal");
    $panelaudio      = upload_gambar("interior_panel_audio", $folderInterior, "panelaudio-$tanggal");
    $lampukabin      = upload_gambar("interior_lampu_kabin", $folderInterior, "lampukabin-$tanggal");
    $interior_bersih  = upload_gambar("interior_bersih", $folderInterior, "interior_bersih-$tanggal");
    $toolkitdongkrak = upload_gambar("interior_toolkit_dongkrak", $folderInterior, "toolkitdongkrak-$tanggal");

    // Simpan ke DB
    $insert = "
    INSERT INTO cek_kendaraan
    (user_id, mobil_id, kilometer, lokasi, komentar, foto_bukti, 
     depan, belakang, kanan, kiri,
     joksabuk, acventilasi, panelaudio, lampukabin, interior_bersih, toolkitdongkrak)
    VALUES (
        '{$dataNip['id_user']}',
        '{$dataMobil['id_mobil']}',
        '$kilometer',
        '$lokasi',
        '$komentar',
        '$foto_bukti',
        '$depan',
        '$belakang',
        '$kanan',
        '$kiri',
        '$joksabuk',
        '$acventilasi',
        '$panelaudio',
        '$lampukabin',
        '$interior_bersih',
        '$toolkitdongkrak'
    )";

    if (mysqli_query($conn, $insert)) {
        echo "<script>alert('Data berhasil disimpan!');</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Check Up Mobil</title>

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
    padding: 20px;
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.header {
    text-align: center;
    margin-bottom: 30px;
    animation: fadeInDown 0.6s ease;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.header-text {
    text-align: left;
}

.logo-hpi {
    width: 90px;
    height: 90px;
    background: white;
    border-radius: 20px;
    padding: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.logo-hpi:hover {
    transform: scale(1.05) rotate(5deg);
}

h2 {
    color: white;
    font-size: 28px;
    font-weight: 600;
    text-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    margin-bottom: 5px;
}

.subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    font-weight: 400;
}

form {
    background: rgba(218, 218, 218, 0.95);
    backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 0.6s ease;
}

.form-section {
    margin-bottom: 35px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #3a6edc;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
    border-radius: 2px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

input[type="text"],
input[type="number"],
textarea {
    width: 100%;
    padding: 12px 16px;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    color: #333;
    transition: all 0.3s ease;
}

input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus {
    outline: none;
    border-color: #3a6edc;
    background: white;
    box-shadow: 0 0 0 3px rgba(58, 110, 220, 0.1);
}

input[type="text"]:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
}

textarea {
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}

.file-group {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.file-group:hover {
    border-color: #3a6edc;
    background: white;
    box-shadow: 0 4px 12px rgba(58, 110, 220, 0.15);
}

.file-group-content {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.file-label {
    font-size: 14px;
    font-weight: 500;
    color: #495057;
    min-width: 180px;
}

.input-file {
    display: none;
}

.label-upload {
    background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
    background-size: 200% 200%;
    border: none;
    color: white;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.label-upload:hover {
    background-position: right center;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(106, 27, 154, 0.4);
    color: white;
}

.label-upload::before {
    /* content: '📷'; */
    font-size: 16px;
}

.preview-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: none;
    transition: transform 0.3s ease;
}

.preview-img:hover {
    transform: scale(1.5);
    z-index: 10;
}

.file-name {
    font-size: 13px;
    color: #6c757d;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-style: italic;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}

.button-group {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 30px;
    flex-wrap: wrap;
}

button {
    padding: 14px 32px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

button[type="submit"] {
    background: linear-gradient(135deg, #3a6edc, #6a1b9a);
    background-size: 200% 200%;
    color: white;
}

button[type="submit"]:hover {
    background-position: right center;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 20px rgba(58, 110, 220, 0.5);
}

button[type="button"] {
    background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            background-size: 200% 200%;
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
}

button[type="button"]:hover {
     background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 27, 154, 0.4);
            color: white;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 15px;
    }

    form {
        padding: 25px;
    }

    .header-content {
        flex-direction: column;
        gap: 15px;
    }

    .header-text {
        text-align: center;
    }

    h2 {
        font-size: 24px;
    }

    .logo-hpi {
        width: 70px;
        height: 70px;
    }

    .file-group-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .file-label {
        min-width: auto;
        width: 100%;
    }

    .photo-grid {
        grid-template-columns: 1fr;
    }

    .button-group {
        justify-content: stretch;
    }

    button {
        flex: 1;
        min-width: 120px;
    }

    .preview-img:hover {
        transform: scale(2);
    }
}

@media (max-width: 480px) {
    form {
        padding: 20px;
    }

    .section-title {
        font-size: 16px;
    }

    button {
        padding: 12px 24px;
        font-size: 14px;
    }
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-content">
            <img src="gambar/hpi.png" class="logo-hpi" alt="HPI Logo">
            <label>cabang medan</label>
            <div class="header-text">
                <h2>Form Check Up Mobil</h2>
                <p class="subtitle">Sistem Pemeriksaan Kendaraan</p>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        
        <!-- INFORMASI PEGAWAI & MOBIL -->
        <div class="form-section">
            <div class="section-title"> Informasi Pegawai & Kendaraan</div>
            
            <div class="form-group">
                <label>NIP:</label>
                <input type="text" value="<?php echo $dataNip['nip']; ?>" readonly disabled>
            </div>

            <div class="form-group">
                <label>Nama:</label>
                <input type="text" value="<?php echo $dataNip['nama']; ?>" readonly disabled>
            </div>

            <div class="form-group">
                <label>No Plat:</label>
                <input type="text" value="<?php echo $dataMobil['plat']; ?>" readonly disabled>
            </div>

            <div class="form-group">
                <label>Tipe Mobil:</label>
                <input type="text" value="<?php echo $dataMobil['tipe_mobil']; ?>" readonly disabled>
            </div>
        </div>

        <!-- DATA PEMERIKSAAN -->
        <div class="form-section">
            <div class="section-title"> Data Pemeriksaan</div>
            
            <div class="form-group">
                <label>Lokasi:</label>
                <input type="text" name="lokasi" placeholder="Masukkan lokasi pemeriksaan" required>
            </div>

            <div class="form-group">
                <label>Kilometer:</label>
                <input type="text" name="kilometer" id="kilometer" placeholder="Masukkan odometer saat ini" maxlength="6" pattern="[0-9]*" inputmode="numeric" required>
            </div>
        </div>

        <!-- FOTO ODOMETER -->
        <div class="form-section">
            <div class="section-title"> Bukti Kilometer & Panel</div>
            
            <div class="file-group">
                <div class="file-group-content">
                    <span class="file-label">Foto Odometer & Panel:</span>
                    <button type="button" class="label-upload" onclick="openCamera('bukti_kilometer')">📷 Ambil Gambar</button>
                    <input type="file" id="bukti_kilometer" name="bukti_kilometer" class="input-file" accept="image/*" capture="environment"
                        onchange="previewImage(this, 'preview_odometer', 'filename_odometer')" required>
                    <img id="preview_odometer" class="preview-img">
                    <span id="filename_odometer" class="file-name"></span>
                </div>
            </div>
        </div>

        <!-- FOTO EKSTERIOR MOBIL -->
        <div class="form-section">
            <div class="section-title"> Foto Eksterior Kendaraan</div>
            
            <div class="photo-grid">
                <?php 
                $sides = [
                    'depan' => 'Tampak Depan',
                    'belakang' => 'Tampak Belakang',
                    'kanan' => 'Tampak Kanan',
                    'kiri' => 'Tampak Kiri'
                ];
                foreach($sides as $side => $label) {
                    echo '<div class="file-group">';
                    echo '<div class="file-group-content">';
                    echo '<span class="file-label">'.$label.':</span>';
                    echo '<button type="button" class="label-upload" onclick="openCamera(\'tampak_'.$side.'\')">📷 Ambil Gambar</button>';
                    echo '<input type="file" id="tampak_'.$side.'" name="tampak_'.$side.'" class="input-file" accept="image/*" capture="environment"
                            onchange="previewImage(this, \'preview_'.$side.'\', \'filename_'.$side.'\')" required>';
                    echo '<img id="preview_'.$side.'" class="preview-img">';
                    echo '<span id="filename_'.$side.'" class="file-name"></span>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- FOTO INTERIOR -->
        <div class="form-section">
            <div class="section-title"> Foto Interior Kendaraan</div>
            
            <div class="photo-grid">
                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">Jok & Sabuk Pengaman:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_joksabuk')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_joksabuk" name="interior_jok_sabuk" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_jok','file_jok')">
                        <img id="prev_jok" class="preview-img">
                        <span id="file_jok" class="file-name"></span>
                    </div>
                </div>

                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">AC & Ventilasi:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_acventilasi')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_acventilasi" name="interior_ac_ventilasi" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_ac','file_ac')">
                        <img id="prev_ac" class="preview-img">
                        <span id="file_ac" class="file-name"></span>
                    </div>
                </div>

                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">Audio:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_panelaudio')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_panelaudio" name="interior_panel_audio" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_panel','file_panel')">
                        <img id="prev_panel" class="preview-img">
                        <span id="file_panel" class="file-name"></span>
                    </div>
                </div>

                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">Lampu Kabin:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_lampukabin')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_lampukabin" name="interior_lampu_kabin" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_lampu','file_lampu')">
                        <img id="prev_lampu" class="preview-img">
                        <span id="file_lampu" class="file-name"></span>
                    </div>
                </div>

                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">Kebersihan Interior:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_bersih')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_bersih" name="interior_bersih" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_bersih','file_bersih')">
                        <img id="prev_bersih" class="preview-img">
                        <span id="file_bersih" class="file-name"></span>
                    </div>
                </div>

                <div class="file-group">
                    <div class="file-group-content">
                        <span class="file-label">Toolkit & Dongkrak:</span>
                        <button type="button" class="label-upload" onclick="openCamera('interior_toolkitdongkrak')">📷 Ambil Gambar</button>
                        <input type="file" id="interior_toolkitdongkrak" name="interior_toolkit_dongkrak" class="input-file"
                            accept="image/*" capture="environment" onchange="previewImage(this,'prev_toolkit','file_toolkit')">
                        <img id="prev_toolkit" class="preview-img">
                        <span id="file_toolkit" class="file-name"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KELUHAN -->
        <div class="form-section">
            <div class="section-title"> Catatan & Keluhan</div>
            
            <div class="form-group">
                <label>Keluhan atau Catatan Tambahan:</label>
                <textarea name="komentar" placeholder="Tuliskan keluhan atau catatan kondisi kendaraan..." required></textarea>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="button-group">
            <button type="button" onclick="window.location.href='login.php'">Kembali</button>
            <button type="submit" name="submit">Simpan Data</button>
        </div>
    </form>
</div>

<script>
function previewImage(input, previewId, filenameId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    const filename = document.getElementById(filenameId);

    if (file) {
        preview.style.display = "block";
        preview.src = URL.createObjectURL(file);
        filename.textContent = file.name;
    } else {
        preview.style.display = "none";
        filename.textContent = "";
    }
}

// Validasi input kilometer - hanya angka
document.addEventListener('DOMContentLoaded', function() {
    const kilometerInput = document.getElementById('kilometer');
    if (kilometerInput) {
        kilometerInput.addEventListener('input', function(e) {
            // Hapus karakter selain angka
            this.value = this.value.replace(/[^0-9]/g, '');
            // Batasi max 6 karakter
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
        
        // Cegah paste yang bukan angka
        kilometerInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const cleanText = pastedText.replace(/[^0-9]/g, '').slice(0, 6);
            this.value = cleanText;
        });
    }
});

// Fungsi untuk membuka kamera WebRTC
async function openCamera(inputId) {
    try {
        // Deteksi device type
        const isMobile = /iPhone|iPad|iPod|Android|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        // Jika mobile, gunakan native file input dengan capture
        if (isMobile) {
            console.log('Mobile detected, using native capture');
            document.getElementById(inputId).click();
            return;
        }
        
        // Cek support browser untuk desktop
        const hasGetUserMedia = !!(navigator && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        
        if (!hasGetUserMedia) {
            console.error('Browser tidak support getUserMedia');
            document.getElementById(inputId).click();
            return;
        }
        
        // Buat container untuk kamera
        const cameraModal = document.createElement('div');
        cameraModal.id = 'camera-modal-' + inputId;
        cameraModal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            gap: 20px;
            padding: 20px;
        `;
        
        const video = document.createElement('video');
        video.style.cssText = `
            max-width: 100%;
            max-height: 60vh;
            border-radius: 10px;
            transform: scaleX(-1);
        `;
        video.autoplay = true;
        video.playsinline = true;
        video.muted = true;
        
        const canvas = document.createElement('canvas');
        canvas.style.display = 'none';
        
        const buttonContainer = document.createElement('div');
        buttonContainer.style.cssText = `
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        `;
        
        const captureBtn = document.createElement('button');
        captureBtn.type = 'button';
        captureBtn.textContent = '📷 Ambil Gambar';
        captureBtn.style.cssText = `
            padding: 12px 30px;
            background: linear-gradient(135deg, #3a6edc, #6a1b9a);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        `;
        captureBtn.onmouseover = () => captureBtn.style.transform = 'scale(1.05)';
        captureBtn.onmouseout = () => captureBtn.style.transform = 'scale(1)';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.textContent = '❌ Batal';
        cancelBtn.style.cssText = `
            padding: 12px 30px;
            background: #ff5c5c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        `;
        cancelBtn.onmouseover = () => cancelBtn.style.transform = 'scale(1.05)';
        cancelBtn.onmouseout = () => cancelBtn.style.transform = 'scale(1)';
        
        buttonContainer.appendChild(captureBtn);
        buttonContainer.appendChild(cancelBtn);
        
        cameraModal.appendChild(video);
        cameraModal.appendChild(buttonContainer);
        cameraModal.appendChild(canvas);
        document.body.appendChild(cameraModal);
        
        // Akses kamera dengan error handling
        let stream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
        } catch (err) {
            console.error('Error detail:', err);
            cameraModal.remove();
            console.log('Fallback ke native file input');
            document.getElementById(inputId).click();
            return;
        }
        
        video.srcObject = stream;
        
        // Tunggu video siap
        video.onloadedmetadata = () => {
            video.play().catch(e => console.error('Play error:', e));
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        };
        
        let isCapturing = false;
        let isModalOpen = true;
        
        // Event listener untuk capture - hanya bisa sekali
        captureBtn.onclick = () => {
            if (isCapturing || !isModalOpen) return;
            isCapturing = true;
            captureBtn.disabled = true;
            
            try {
                const ctx = canvas.getContext('2d');
                if (ctx) {
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, -canvas.width, 0);
                    
                    canvas.toBlob((blob) => {
                        if (!isModalOpen || !blob) return;
                        
                        const file = new File([blob], 'camera-photo-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        
                        const inputElement = document.getElementById(inputId);
                        if (inputElement) {
                            inputElement.files = dataTransfer.files;
                            
                            // Trigger onchange event
                            const event = new Event('change', { bubbles: true });
                            inputElement.dispatchEvent(event);
                        }
                        
                        // Stop stream dan tutup modal
                        stream.getTracks().forEach(track => track.stop());
                        isModalOpen = false;
                        cameraModal.remove();
                    }, 'image/jpeg', 0.95);
                }
            } catch (e) {
                console.error('Capture error:', e);
                alert('Error saat mengambil foto');
            }
        };
        
        // Event listener untuk cancel
        cancelBtn.onclick = () => {
            if (!isModalOpen) return;
            isModalOpen = false;
            stream.getTracks().forEach(track => track.stop());
            cameraModal.remove();
        };
        
    } catch (error) {
        console.error('Error mengakses kamera:', error);
        // Fallback ke file picker
        document.getElementById(inputId).click();
    }
}
</script>

</body>
</html>