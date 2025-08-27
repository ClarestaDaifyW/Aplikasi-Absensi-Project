<?php
session_start();
include '../config/koneksi.php';

$user_id = $_SESSION['user_id'];
$tanggal = date("Y-m-d");

// Ambil data presensi hari ini
$q = mysqli_query($conn, "SELECT * FROM presensi WHERE user_id='$user_id' AND tanggal='$tanggal'");
$presensi = mysqli_fetch_assoc($q);

// Proses jam masuk
if (isset($_POST['masuk'])) {
    $jam_masuk = $_POST['jam_masuk'];
    mysqli_query($conn, "INSERT INTO presensi (user_id, tanggal, jam_masuk) VALUES ('$user_id', '$tanggal', '$jam_masuk')");
    header("Location: presensi.php");
    exit;
}

// Proses jam keluar
if (isset($_POST['keluar'])) {
    $jam_keluar = $_POST['jam_keluar'];
    mysqli_query($conn, "UPDATE presensi SET jam_keluar='$jam_keluar' WHERE user_id='$user_id' AND tanggal='$tanggal'");
    header("Location: presensi.php");
    exit;
}

// Untuk tampilan status
$showMasuk = false;
$showKeluar = false;
$showComplete = false;
$jamMasuk = '';
$jamKeluar = '';
$totalJam = '';

if (!$presensi) {
    $showMasuk = true;
} elseif ($presensi && !$presensi['jam_keluar']) {
    $showKeluar = true;
    $jamMasuk = $presensi['jam_masuk'];
} else {
    $showComplete = true;
    $jamMasuk = $presensi['jam_masuk'];
    $jamKeluar = $presensi['jam_keluar'];
    // Hitung total jam kerja
    $start = strtotime($jamMasuk);
    $end = strtotime($jamKeluar);
    $diff = $end - $start;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $totalJam = $hours . ' jam ' . ($minutes > 0 ? $minutes . ' menit' : '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Presensi Modern</title>
    <style>
        /* ...CSS dari prompt, tidak diubah... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h3 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        .date-info {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .date-info .date {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .date-info .time {
            font-size: 1rem;
            opacity: 0.9;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        input[type="time"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: white;
            color: #2c3e50;
        }
        input[type="time"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn-masuk {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }
        .btn-masuk:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(46, 204, 113, 0.4);
        }
        .btn-keluar {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
        }
        .btn-keluar:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(231, 76, 60, 0.4);
        }
        .status-complete {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }
        .status-complete h4 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .time-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .time-info p {
            margin: 8px 0;
            font-size: 1.1rem;
        }
        .icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1);}
            50% { transform: scale(1.05);}
            100% { transform: scale(1);}
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px);}
            to { opacity: 1; transform: translateY(0);}
        }
        @media (max-width: 768px) {
            .container { padding: 20px; margin: 10px;}
            .header h3 { font-size: 2rem;}
            .btn { padding: 12px; font-size: 1rem;}
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .success-checkmark {
            display: inline-block;
            width: 22px;
            height: 22px;
            margin-right: 10px;
            transform: rotate(45deg);
        }
        .success-checkmark::before {
            content: '';
            position: absolute;
            width: 3px;
            height: 9px;
            background: white;
            left: 11px;
            top: 6px;
        }
        .success-checkmark::after {
            content: '';
            position: absolute;
            width: 6px;
            height: 3px;
            background: white;
            left: 6px;
            top: 12px;
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <div class="header">
            <h3>📍 Sistem Presensi</h3>
        </div>
        <div style="text-align:center; margin-bottom:20px;">
            <a href="dashboard.php" style="
                display:inline-block;
                background:linear-gradient(45deg,#4f8cff,#2563eb);
                color:#fff;
                padding:10px 22px;
                border-radius:8px;
                text-decoration:none;
                font-weight:600;
                box-shadow:0 2px 8px rgba(79,140,255,0.10);
                transition:background 0.2s;
            " onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='linear-gradient(45deg,#4f8cff,#2563eb)'">
                ← Kembali ke Dashboard
            </a>
        </div>
        <div class="date-info">
            <div class="date" id="currentDate"></div>
            <div class="time" id="currentTime"></div>
        </div>

        <!-- Form Jam Masuk -->
        <?php if ($showMasuk): ?>
        <div class="form-container" id="form-masuk">
            <div class="icon">🌅</div>
            <h4 style="margin-bottom: 20px; color: #2c3e50;">Selamat Pagi! Silakan absen masuk</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="jam_masuk">Jam Masuk:</label>
                    <input type="time" name="jam_masuk" id="jam_masuk" required>
                </div>
                <button type="submit" name="masuk" class="btn btn-masuk pulse">
                    <span class="success-checkmark"></span>
                    Absen Masuk
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Form Jam Keluar -->
        <?php if ($showKeluar): ?>
        <div class="form-container" id="form-keluar">
            <div class="icon">🌇</div>
            <h4 style="margin-bottom: 20px; color: #2c3e50;">Waktu pulang! Silakan absen keluar</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="jam_keluar">Jam Keluar:</label>
                    <input type="time" name="jam_keluar" id="jam_keluar" required>
                </div>
                <button type="submit" name="keluar" class="btn btn-keluar pulse">
                    <span class="success-checkmark"></span>
                    Absen Pulang
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Status Presensi Lengkap -->
        <?php if ($showComplete): ?>
        <div class="status-complete" id="status-complete">
            <div class="icon">✅</div>
            <h4>Presensi Hari Ini Sudah Lengkap!</h4>
            <div class="time-info">
                <p><strong>Jam Masuk:</strong> <span id="jam-masuk-display"><?= htmlspecialchars($jamMasuk) ?></span></p>
                <p><strong>Jam Keluar:</strong> <span id="jam-keluar-display"><?= htmlspecialchars($jamKeluar) ?></span></p>
                <p><strong>Total Jam Kerja:</strong> <span id="total-jam"><?= htmlspecialchars($totalJam) ?></span></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            document.getElementById('currentDate').textContent = 
                now.toLocaleDateString('id-ID', options);
            document.getElementById('currentTime').textContent = 
                now.toLocaleTimeString('id-ID');
        }

        // Set waktu saat ini sebagai default pada input time
        function setCurrentTime() {
            const now = new Date();
            const timeString = now.toTimeString().slice(0, 5);
            const jamMasukInput = document.getElementById('jam_masuk');
            const jamKeluarInput = document.getElementById('jam_keluar');
            if (jamMasukInput && !jamMasukInput.value) {
                jamMasukInput.value = timeString;
            }
            if (jamKeluarInput && !jamKeluarInput.value) {
                jamKeluarInput.value = timeString;
            }
        }

        updateTime();
        setCurrentTime();
        setInterval(updateTime, 1000);
        setInterval(setCurrentTime, 60000);
    </script>
</body>
</html>
