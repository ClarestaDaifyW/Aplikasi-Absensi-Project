<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");



// Ambil presensi hari ini
$q_presensi = mysqli_query($conn, "SELECT * FROM presensi WHERE user_id='$user_id' AND tanggal='$tanggal_hari_ini'");
$presensi = mysqli_fetch_assoc($q_presensi);

// Ambil aktivitas hari ini
$q_aktivitas = mysqli_query($conn, "SELECT * FROM aktivitas WHERE user_id='$user_id' AND tanggal='$tanggal_hari_ini'");
$aktivitas = mysqli_fetch_assoc($q_aktivitas);

// Data untuk profile
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($q_user);

// Proses update profile
if (isset($_POST['update_profile'])) {
    $new_nama = mysqli_real_escape_string($conn, $_POST['edit_nama']);
    $new_username = mysqli_real_escape_string($conn, $_POST['edit_username']);
    $new_kelas = mysqli_real_escape_string($conn, $_POST['edit_kelas']);
    $new_jurusan = mysqli_real_escape_string($conn, $_POST['edit_jurusan']);
    
    // Validasi input
    if (empty($new_nama) || empty($new_username) || empty($new_kelas) || empty($new_jurusan)) {
        $error_message = "Semua field harus diisi!";
    } else {
        // Cek apakah username sudah digunakan user lain
        $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username='$new_username' AND id != '$user_id'");
        if (mysqli_num_rows($check_username) > 0) {
            $error_message = "Username sudah digunakan oleh user lain!";
        } else {
            // Update data
            $update = mysqli_query($conn, "UPDATE users SET nama='$new_nama', username='$new_username', kelas='$new_kelas', jurusan='$new_jurusan' WHERE id='$user_id'");
            
            if ($update) {
                $_SESSION['username'] = $new_username;
                $success_message = "Profil berhasil diupdate!";
                
                // Refresh data user
                $q_user = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
                $user = mysqli_fetch_assoc($q_user);
            } else {
                $error_message = "Gagal update profil! " . mysqli_error($conn);
            }
        }
    }
}

// Cek kelengkapan data profil
$notif_incomplete_profile = false;
if (
    empty($user['nama']) ||
    empty($user['username']) ||
    empty($user['kelas']) ||
    empty($user['jurusan'])
) {
    $notif_incomplete_profile = true;
}

//upload foto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $targetDir = "uploads/";
        // Buat folder jika belum ada
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
            $foto = $fileName;
        }
    }
    $query = "INSERT INTO aktivitas (user_id, tanggal, deskripsi, foto, status_validasi) 
              VALUES ('$user_id', '$tanggal_hari_ini', '$deskripsi', '$foto', 'pending')";
    mysqli_query($conn, $query);
    header("Location: dashboard.php?success=1");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            min-height: 100vh;
        }
        .container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4f8cff 0%, #3b82f6 100%);
            color: white;
            padding: 0;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        .sidebar-header {
            padding: 30px 25px 25px 25px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-header h2 { font-size: 1.4rem; margin-bottom: 8px; color: white; }
        .sidebar-header p { font-size: 0.9rem; opacity: 0.8; color: white; }
        .sidebar-menu { padding: 20px 0; }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            cursor: pointer;
        }
        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }
        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: white;
            font-weight: 600;
        }
        .menu-item .icon { margin-right: 15px; font-size: 1.2rem; width: 24px; text-align: center; }
.logout-item {
    position: absolute;
    bottom: 20px;
    width: 100%;
    padding: 15px 25px;
    background: transparent; /* Hapus background merah */
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout-item:hover {
    background: rgba(255, 255, 255, 0.1); /* Efek hover yang lebih subtle */
}


        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        .content-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        .content-header h1 { color: #2d3a4b; font-size: 2rem; margin-bottom: 8px; }
        .content-header p { color: #64748b; font-size: 1rem; }
        .content-section { display: none; animation: fadeIn 0.5s ease; }
        .content-section.active { display: block; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(79, 140, 255, 0.1);
        }
        .card h3 { color: #2d3a4b; margin-bottom: 15px; font-size: 1.3rem; display: flex; align-items: center; }
        .card h3 .icon { margin-right: 10px; color: #4f8cff; }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-item:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 500; }
        .info-value { color: #2d3a4b; font-weight: 600; }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-valid { background: #dcfce7; color: #166534; }
        .status-invalid { background: #f9efefff; color: #e12626ff; }
        .status-pending { background: #fef3c7; color: #d97706; }

        .status-disetujui { 
            background: #dcfce7; 
            color: #166534; 
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4f8cff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 140, 255, 0.3);
        }
        .btn-outline { 
            background: transparent; 
            color: #4f8cff; 
            border: 2px solid #4f8cff; 
        }
        .btn-outline:hover { 
            background: #4f8cff; 
            color: white; 
        }
        .btn-success { 
            background: #22c55e; 
        }
        .btn-success:hover { 
            background: #16a34a; 
        }
        .btn-warning { 
            background: #f59e0b; 
        }
        .btn-warning:hover { 
            background: #d97706; 
        }
        .btn-danger { 
            background: #ef4444; 
        }
        .btn-danger:hover { 
            background: #dc2626; 
        }
        .form-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3a4b;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #2d3a4b;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #4f8cff;
            box-shadow: 0 0 0 3px rgba(79, 140, 255, 0.1);
        }
        .form-group input::placeholder, .form-group textarea::placeholder { 
            color: #94a3b8; 
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); z-index: 1000; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: #4f8cff;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
            }
        }
        .mobile-toggle { display: none; }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .overlay.active { display: block; }
        .floating-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            background:rgb(245, 85, 11);
            color: white;
            padding: 8px 18px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideIn 0.5s ease-out, fadeOut 0.5s ease-in 4.5s forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        /* Light Mode (default) */
        body {
          background: #ffffff;
          color: #000000;
          font-family: Arial, sans-serif;
          transition: all 0.3s ease;
        }

        /* Dark Mode */
        body.dark-mode {
          background: #121212;
          color: #ffffff;
        }

        /* PERBAIKAN: Warna teks header dashboard di mode gelap */
body.dark-mode .content-header h1 {
  color: #ffffff;
}
body.dark-mode .content-header p {
    color: #e2dedeec;
}

        body.dark-mode .card,
        body.dark-mode .content-header {
          background: #1e1e1e;
          color: #ffffff;
          border-color: #333;
        }

        body.dark-mode .info-label,
        body.dark-mode .info-value,
        body.dark-mode .card h3 {
          color: #ffffff;
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group textarea {
          background: #2d2d2d;
          color: #ffffff;
          border-color: #444;
        }

        /* Style khusus untuk input file */
        input[type="file"] {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #6b7280; /* Abu-abu gelap */
            width: 220px;
            color: white; /* Teks putih untuk kontras */
            cursor: pointer;
        }
        
        /* Style untuk mode gelap */
        body.dark-mode input[type="file"] {
            background: #464b54ff; 
            color: #e5e7eb;
        }

        /* Style untuk dark mode sidebar */
body.dark-mode .sidebar {
    background: linear-gradient(180deg, #374151 0%, #1f2937 100%);
}

body.dark-mode .sidebar-header {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

body.dark-mode .menu-item {
    color: #e5e7eb;
}

body.dark-mode .menu-item:hover {
    background: rgba(255, 255, 255, 0.08);
}

body.dark-mode .menu-item.active {
    background: rgba(255, 255, 255, 0.12);
}

body.dark-mode .logout-item {
    background: rgba(239, 68, 68, 0.15);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

body.dark-mode .logout-item:hover {
    background: rgba(239, 68, 68, 0.25);
}
        
        /* TAMBAHKAN style untuk placeholder teks pada input file */
input[type="file"]::file-selector-button {
    color: white;
    background: #4b5563;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    margin-right: 10px;
    cursor: pointer;
}

body.dark-mode input[type="file"]::file-selector-button {
    background: #374151;
    color: #e5e7eb;
}

/* TAMBAHKAN style untuk preview gambar */
#previewImg {
    display: none;
    max-width: 90px;
    max-height: 90px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #f8f9fa;
}

body.dark-mode #previewImg {
    border-color: #6b7280;
    background: #4b5563;
}

/* Gaya khusus untuk input file */
.file-input-container {
    position: relative;
    display: inline-block;
    width: 100%;
    margin-bottom: 8px;
}

.file-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #6b7280;
    border-radius: 8px;
    padding: 8px;
    width: 220px;
}

.file-input-wrapper input[type="file"] {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-button {
    padding: 8px 12px;
    background: #4b5563;
    color: white;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;
}

.file-name-display {
    margin-left: 10px;
    color: white;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

/* Untuk mode gelap */
body.dark-mode .file-input-wrapper {
    background: #4b5563;
}

body.dark-mode .file-input-button {
    background: #374151;
}

/* Style untuk preview gambar */
#previewImg {
    display: none;
    max-width: 90px;
    max-height: 90px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #f8f9fa;
}

body.dark-mode #previewImg {
    border-color: #6b7280;
    background: #4b5563;
}
        /* PERBAIKAN: Warna teks pada form aktivitas di mode gelap */
body.dark-mode .form-group label {
    color: #ffffff !important;
}

body.dark-mode .form-group small {
    color: #e2dedeec !important;
}

        header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 15px;
          background: #f4f4f4;
        }

        body.dark-mode header {
          background: #1e1e1e;
        }

        button {
          padding: 10px 20px;
          border: none;
          border-radius: 8px;
          cursor: pointer;
        }

        table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 15px;
        }

        th, td {
          padding: 12px;
          text-align: left;
          border-bottom: 1px solid #e2e8f0;
        }

        body.dark-mode th,
        body.dark-mode td {
          border-color: #333;
          color: #fff;
        }

        th {
          background: #f8fafc;
          font-weight: 600;
        }

        body.dark-mode th {
          background: #2d2d2d;
        }

        /* Style untuk modal gambar */
#imageModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

#imageModal img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 5px;
}

#imageModal span {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 30px;
    cursor: pointer;
}
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
    <div class="overlay" onclick="toggleSidebar()"></div>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>📚 Portal Siswa</h2>
                <p>Selamat datang, <?= htmlspecialchars($user['nama'] ?? 'Siswa'); ?>!</p>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active" onclick="showSection('dashboard', event)">
                    Dashboard
                </a>
                <a href="#" class="menu-item" onclick="showSection('presensi', event)">
                    Presensi
                </a>
                <a href="#" class="menu-item" onclick="showSection('aktivitas', event)">
                    Aktivitas
                </a>
                <a href="#" class="menu-item" onclick="showSection('riwayat', event)">
                    Riwayat
                </a>
                <a href="#" class="menu-item" onclick="showSection('profile', event)">
                    Profile
                </a>
            </div>
            <div class="logout-item">
                <a href="#" class="menu-item" onclick="logout()">
                    <span class="icon">➜]</span>
                    Keluar
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="content-header" style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h1 style="margin-bottom: 8px;">Dashboard</h1>
                        <p>Ringkasan aktivitas hari ini - <span id="currentDate"></span></p>
                    </div>
                    <button id="darkModeToggle" style="margin-left: 24px; padding: 10px 20px; border-radius: 8px; border: none;">
                        🌙 Dark Mode
                    </button>
                </div>
                <div class="card">
                    <h3><span class="icon">📅</span>Presensi Hari Ini</h3>
                    <div class="info-item">
                        <span class="info-label">Jam Masuk:</span>
                        <span class="info-value" id="jam-masuk"><?= $presensi && $presensi['jam_masuk'] ? htmlspecialchars($presensi['jam_masuk']) : '-' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jam Keluar:</span>
                        <span class="info-value" id="jam-keluar"><?= $presensi && $presensi['jam_keluar'] ? htmlspecialchars($presensi['jam_keluar']) : '-' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <?php if ($presensi && $presensi['jam_masuk']): ?>
                            <span class="status-badge status-valid">Hadir</span>
                        <?php else: ?>
                            <span class="status-badge status-invalid">Belum Hadir</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <h3><span class="icon">📋</span>Aktivitas Hari Ini</h3>
                    <div class="info-item">
                        <span class="info-label">Deskripsi:</span>
                        <span class="info-value"><?= $aktivitas ? htmlspecialchars($aktivitas['deskripsi']) : '-' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Validasi:</span>
                        <?php if ($aktivitas): ?>
                            <?php if ($aktivitas['status_validasi'] == 'Valid'): ?>
                                <span class="status-badge status-valid">Valid</span>
                            <?php elseif ($aktivitas['status_validasi'] == 'pending'): ?>
                                <span class="status-badge status-pending">Menunggu</span>
                            <?php else: ?>
                                <span class="status-badge status-valid"><?= htmlspecialchars($aktivitas['status_validasi']) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="status-badge status-invalid">Belum Ada</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Presensi Section -->
            <div id="presensi" class="content-section">
                <div class="content-header">
                    <h1>Presensi</h1>
                    <p>Kelola presensi harian Anda</p>
                </div>
                <div class="form-container">
                    <h3 style="margin-bottom: 20px; color: white;">🌅 Presensi Masuk</h3>
                    <form method="POST" action="presensi.php">
                        <div class="form-group">
                            <label for="jam_masuk" style="color: white;">Jam Masuk:</label>
                            <input type="time" id="jam_masuk" name="jam_masuk" required>
                        </div>
                        <button type="submit" name="masuk" class="btn btn-success">Absen Masuk</button>
                    </form>
                </div>
                <div class="form-container" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <h3 style="margin-bottom: 20px; color: white;">🌇 Presensi Keluar</h3>
                    <form method="POST" action="presensi.php">
                        <div class="form-group">
                            <label for="jam_keluar" style="color: white;">Jam Keluar:</label>
                            <input type="time" id="jam_keluar" name="jam_keluar" required>
                        </div>
                        <button type="submit" name="keluar" class="btn btn-warning">Absen Keluar</button>
                    </form>
                </div>
            </div>
            
            <!-- Aktivitas Section -->
            <div id="aktivitas" class="content-section">
                <div class="content-header">
                    <h1>Aktivitas</h1>
                    <p>Catat aktivitas pembelajaran Anda</p>
                </div>
                <div class="card">
                    <h3><span class="icon">📝</span>Input Aktivitas</h3>
                    <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Aktivitas:</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan deskripsi aktivitas hari ini..." required></textarea>
                        </div>
                     <div class="form-group">
    <label for="foto" style="font-weight:400; color:#2d3a4b; margin-bottom:8px;">Upload Gambar Aktivitas:</label>
    <div style="display:flex; align-items:center; gap:18px; flex-wrap: wrap;">
        <div class="file-input-container">
            <div class="file-input-wrapper">
                <div class="file-input-button">Pilih File</div>
                <span class="file-name-display" id="file-name-display">No file chosen</span>
                <input type="file" name="foto" id="fotoInput" accept="image/*">
            </div>
        </div>
        <img id="previewImg" src="#" alt="Preview">
    </div>
    <small style="color:#64748b; margin-top:6px; display:block;">Format gambar: JPG, PNG, maksimal 2MB.</small>
</div>
                        <button type="submit" class="btn">Simpan Aktivitas</button>
                    </form>
                </div>
            </div>
            
            <!-- Riwayat Section -->
            <div id="riwayat" class="content-section">
                <div class="content-header">
                    <h1>Riwayat</h1>
                    <p>Lihat riwayat presensi dan aktivitas</p>
                </div>
                <div class="card">
                    <h3><span class="icon">📊</span>Riwayat Presensi</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil riwayat presensi
                            $q_riwayat = mysqli_query($conn, "SELECT * FROM presensi WHERE user_id='$user_id' ORDER BY tanggal DESC LIMIT 10");
                            while ($row = mysqli_fetch_assoc($q_riwayat)) :
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                                <td>
                                    <?php if ($row['jam_masuk']): ?>
                                        <span class="status-badge status-valid">Hadir</span>
                                    <?php else: ?>
                                        <span class="status-badge status-invalid">Tidak Hadir</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card">
    <h3><span class="icon">📋</span>Riwayat Aktivitas</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Deskripsi</th>
                <th>Foto</th> <!-- Kolom untuk foto -->
                <th>Status Validasi</th> <!-- Kolom untuk status validasi -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Ambil riwayat aktivitas milik user yang sedang login
            $q_riwayat_aktivitas = mysqli_query($conn, "SELECT * FROM aktivitas WHERE user_id='$user_id' ORDER BY tanggal DESC LIMIT 10");
            while ($row = mysqli_fetch_assoc($q_riwayat_aktivitas)) :
            ?>
            <tr>
                <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                <td>
                    <?php if (!empty($row['foto'])): ?>
                        <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" 
                             alt="Foto Aktivitas" 
                             style="max-width: 80px; max-height: 80px; border-radius: 5px; cursor: pointer;"
                             onclick="showImageModal('uploads/<?= htmlspecialchars($row['foto']) ?>')">
                    <?php else: ?>
                        <span>-</span>
                    <?php endif; ?>
                </td>
                <td>
                   <?php 
                            // Perbaikan logika pengecekan status
                            if ($row['status_validasi'] == 'Disetujui'): ?>
                                <span class="status-badge status-disetujui">DISETUJUI</span>
                            <?php elseif ($row['status_validasi'] == 'Valid'): ?>
                                <span class="status-badge status-valid">Valid</span>
                            <?php elseif ($row['status_validasi'] == 'pending'): ?>
                                <span class="status-badge status-pending">Menunggu</span>
                            <?php else: ?>
                                <span class="status-badge status-valid"><?= htmlspecialchars($row['status_validasi']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
            
            <!-- Profile Section -->
            <div id="profile" class="content-section">
                <div class="content-header">
                    <h1>Profile</h1>
                    <p>Kelola informasi profile Anda</p>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        ✅ <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        ❌ <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <h3><span class="icon">👤</span>Informasi Profile</h3>
                    
                    <!-- Profile View -->
                    <div id="profileView">
                        <div class="info-item">
                            <span class="info-label">Nama:</span>
                            <span class="info-value"><?= htmlspecialchars($user['nama'] ?? '-') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username:</span>
                            <span class="info-value"><?= htmlspecialchars($user['username'] ?? '-') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Role:</span>
                            <span class="info-value"><?= htmlspecialchars($user['role'] ?? '-') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kelas:</span>
                            <span class="info-value"><?= htmlspecialchars($user['kelas'] ?? '-') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jurusan:</span>
                            <span class="info-value"><?= htmlspecialchars($user['jurusan'] ?? '-') ?></span>
                        </div>
                        <div style="margin-top: 20px;">
                            <button type="button" class="btn btn-success" onclick="editProfile()">✏️ Edit Profil</button>
                        </div>
                    </div>
                    
                    <!-- Profile Edit Form -->
                    <div id="formEditProfile" style="display: none;">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="edit_nama">Nama Lengkap:</label>
                                <input type="text" id="edit_nama" name="edit_nama" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required placeholder="Masukkan nama lengkap">
                            </div>
                            <div class="form-group">
                                <label for="edit_username">Username:</label>
                                <input type="text" id="edit_username" name="edit_username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required placeholder="Masukkan username">
                            </div>
                            <div class="form-group">
                                <label for="edit_kelas">Kelas:</label>
                                <input type="text" id="edit_kelas" name="edit_kelas" value="<?= htmlspecialchars($user['kelas'] ?? '') ?>" required placeholder="Contoh: XII IPA 1">
                            </div>
                            <div class="form-group">
                                <label for="edit_jurusan">Jurusan:</label>
                                <input type="text" id="edit_jurusan" name="edit_jurusan" value="<?= htmlspecialchars($user['jurusan'] ?? '') ?>" required placeholder="Contoh: IPA, IPS, Multimedia">
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" name="update_profile" class="btn btn-success">💾 Simpan Perubahan</button>
                                <button type="button" class="btn btn-outline" onclick="batalEdit()">❌ Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="logoutModal" style="display:none; position:fixed; z-index:2000; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; max-width:340px; margin:auto; padding:32px 24px 20px 24px; box-shadow:0 8px 32px rgba(79,140,255,0.18); text-align:center; position:relative;">
                <div style="font-size:1.15rem; font-weight:600; color:#2d3a4b; margin-bottom:10px;">Konfirmasi Keluar</div>
                <div style="color:#64748b; font-size:1rem; margin-bottom:24px;">Apakah Anda yakin ingin keluar dari akun?</div>
                <button onclick="confirmLogout()" style="background:#ef4444; color:#fff; border:none; border-radius:6px; padding:8px 22px; font-size:1rem; font-weight:500; margin-right:10px; cursor:pointer; transition:background 0.2s;">Keluar</button>
                <button onclick="closeLogoutModal()" style="background:#f3f4f6; color:#2d3a4b; border:none; border-radius:6px; padding:8px 22px; font-size:1rem; font-weight:500; cursor:pointer; transition:background 0.2s;">Batal</button>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan foto dalam ukuran besar -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); z-index: 10000; justify-content: center; align-items: center;">
    <div style="position: relative; max-width: 90%; max-height: 90%;">
        <span style="position: absolute; top: -40px; right: 0; color: white; font-size: 30px; cursor: pointer;" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" style="max-width: 100%; max-height: 100%; border-radius: 5px;">
    </div>
</div>

<!-- Modal untuk menampilkan foto dalam ukuran besar -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); z-index: 10000; justify-content: center; align-items: center;">
    <div style="position: relative; max-width: 90%; max-height: 90%;">
        <span style="position: absolute; top: -40px; right: 0; color: white; font-size: 30px; cursor: pointer;" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" style="max-width: 100%; max-height: 100%; border-radius: 5px;">
    </div>
</div>
    
    <?php if ($notif_incomplete_profile): ?>
        <div id="notif-profile-incomplete" class="floating-alert">
            ⚠️ Data profil Anda belum lengkap. Silakan lengkapi data diri Anda di menu <b>Profile</b>!
        </div>
    <?php endif; ?>
    
    <script>

        // JavaScript untuk menampilkan nama file
document.getElementById('fotoInput').addEventListener('change', function(e) {
    const fileNameDisplay = document.getElementById('file-name-display');
    const previewImg = document.getElementById('previewImg');
    
    if (this.files && this.files[0]) {
        fileNameDisplay.textContent = this.files[0].name;
        
        // Preview gambar
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.style.display = 'block';
            previewImg.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    } else {
        fileNameDisplay.textContent = 'No file chosen';
        previewImg.style.display = 'none';
        previewImg.src = '#';
    }
});

     function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Tutup modal ketika mengklik di luar gambar
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target.id === 'imageModal') {
        closeImageModal();
    }
});
        
        function showSection(sectionId, event) {
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Update active menu item
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => item.classList.remove('active'));
            
            if(event) {
                event.target.classList.add('active');
            } else {
                // If no event provided (page load), activate the dashboard menu item
                document.querySelector(`.menu-item[onclick="showSection('${sectionId}', event)"]`).classList.add('active');
            }
            
            // Close sidebar on mobile
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        }
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function logout() {
            document.getElementById('logoutModal').style.display = 'flex';
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }
        
        function confirmLogout() {
            window.location.href = '../auth/logout.php';
        }
        
        // Update tanggal hari ini
        function updateCurrentDate() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
        }
        updateCurrentDate();

        function editProfile() {
            document.getElementById('profileView').style.display = 'none';
            document.getElementById('formEditProfile').style.display = 'block';
        }
        
        function batalEdit() {
            document.getElementById('formEditProfile').style.display = 'none';
            document.getElementById('profileView').style.display = 'block';
        }
        
        // Auto hide alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        const toggle = document.getElementById("darkModeToggle");

        // Cek preferensi sebelumnya
        if (localStorage.getItem("darkMode") === "enabled") {
          document.body.classList.add("dark-mode");
          toggle.textContent = "☀️ Light Mode";
        }

        // Event klik tombol
        toggle.addEventListener("click", () => {
          document.body.classList.toggle("dark-mode");

          if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("darkMode", "enabled");
            toggle.textContent = "☀️ Light Mode";
          } else {
            localStorage.setItem("darkMode", "disabled");
            toggle.textContent = "🌙 Dark Mode";
          }
        });

        // Auto dark mode after 6 PM
        const hour = new Date().getHours();
        if (hour >= 18 || hour < 6) {
          if (localStorage.getItem("darkMode") !== "disabled") {
            document.body.classList.add("dark-mode");
            toggle.textContent = "☀️ Light Mode";
          }
        }
        

        document.getElementById('fotoInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('previewImg');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
});
    </script>
</body>
</html>