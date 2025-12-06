<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {

    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $plat = mysqli_real_escape_string($conn, $_POST['password']); // password = plat

    // Cek apakah nip ada di tabel_user
    $sqlNip = "SELECT * FROM tabel_user WHERE nip = '$nip' LIMIT 1";
    $resultNip = mysqli_query($conn, $sqlNip);

    if (mysqli_num_rows($resultNip) === 1) {

        $dataNip = mysqli_fetch_assoc($resultNip);

        // cek apakah PLAT ada di tabel_mobil
        $sqlPlat = "SELECT * FROM tabel_mobil WHERE plat = '$plat' LIMIT 1";
        $resultPlat = mysqli_query($conn, $sqlPlat);

        if (mysqli_num_rows($resultPlat) === 1) {

            $dataPlat = mysqli_fetch_assoc($resultPlat);

            // LOGIN BERHASIL
            $_SESSION['nip'] = $dataNip['nip'];
            $_SESSION['nama'] = $dataNip['nama'];
            $_SESSION['plat'] = $dataPlat['plat'];
            $_SESSION['tipe_mobil'] = $dataPlat['tipe_mobil'];

            header("Location: pegawai.php");
            exit;

        } else {
            $error = "Nomor plat tidak ditemukan!";
        }

    } else {
        $error = "nip tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Check Up Mobil</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ==== FULL PAGE BACKGROUND ==== */
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(145deg,
                #6a1b9a 0%,
                #3a6edc 25%,
                #ff8f00 50%,
                #d1d1d1 100%
            );
            background-attachment: fixed;
            background-size: cover;
            position: relative;
            overflow: hidden;
        }

        /* Efek glow overlay */
        body::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            top: -100px;
            left: -100px;
            background: radial-gradient(
                circle,
                rgba(106, 27, 154, 0.4) 0%,
                transparent 70%
            );
            filter: blur(80px);
            z-index: 0;
        }

        body::after {
            content: "";
            position: absolute;
            width: 600px;
            height: 600px;
            bottom: -130px;
            right: -150px;
            background: radial-gradient(
                circle,
                rgba(58, 110, 220, 0.4) 0%,
                rgba(255, 143, 0, 0.4) 40%,
                transparent 75%
            );
            filter: blur(110px);
            z-index: 0;
        }

        /* ==== LOGIN WRAPPER ==== */
        .login-wrapper {
            display: flex;
            gap: 35px;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 40px 45px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            z-index: 10;
            position: relative;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ==== LOGO SIDE ==== */
        .logo-side {
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .side-logo {
            width: 140px;
            height: 140px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
            transition: transform 0.3s ease;
        }

        .side-logo:hover {
            transform: scale(1.05) rotate(5deg);
        }

        /* ==== LOGIN CONTAINER ==== */
        .login-container {
            width: 320px;
            position: relative;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 24px;
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        /* Input */
        input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #333;
        }

        input:focus {
            border-color: #3a6edc;
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(58, 110, 220, 0.1);
        }

        input::placeholder {
            color: #999;
        }

        /* Tombol */
        button {
            width: 100%;
            padding: 14px;
            border: none;
            background: linear-gradient(135deg, #6a1b9a, #3a6edc, #ff8f00);
            background-size: 200% 200%;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 27, 154, 0.3);
            margin-top: 10px;
        }

        button:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 27, 154, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        /* Error text */
        .error {
            margin-top: 18px;
            padding: 12px;
            background: rgba(214, 69, 69, 0.1);
            border: 1px solid rgba(214, 69, 69, 0.3);
            border-radius: 8px;
            color: #d64545;
            text-align: center;
            font-size: 14px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                padding: 30px 25px;
                gap: 25px;
            }

            .logo-side {
                padding: 20px;
            }

            .side-logo {
                width: 100px;
                height: 100px;
            }

            .login-container {
                width: 100%;
                max-width: 320px;
            }

            .login-container h2 {
                font-size: 20px;
            }
        }
    </style>

</head>

<body>

<div class="login-wrapper">

    <div class="logo-side">
        <img src="gambar/hpi.png" class="side-logo" alt="HPI Logo">
    </div>

    <div class="login-container">
        <h2>Login Check Up Mobil</h2>

        <form method="POST">
            <div class="form-group">
                <label>NIP</label>
                <input type="number" name="nip" placeholder="Masukkan NIP" required>
            </div>

            <div class="form-group">
                <label>Nomor Plat</label>
                <input type="text" name="password" placeholder="Masukkan Nomor Plat" required>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </div>

</div>

</body>
</html>