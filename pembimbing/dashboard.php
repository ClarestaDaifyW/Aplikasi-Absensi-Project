<?php
session_start();
include '../config/koneksi.php';

// Cegah akses langsung jika bukan pembimbing
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pembimbing') {
  header("Location: ../auth/login.php");
  exit;
}

// Ambil semua data aktivitas siswa (JOIN dengan nama user)
$query = mysqli_query($conn, "
  SELECT aktivitas.*, users.nama 

  FROM aktivitas 
  JOIN users ON aktivitas.user_id = users.id
  ORDER BY tanggal DESC
");

// Ambil data presensi siswa untuk rekap (contoh, bisa dikembangkan)
$query_presensi = mysqli_query($conn, "
  SELECT users.nama, COUNT(presensi.id) as hadir
  FROM users
  LEFT JOIN presensi ON users.id = presensi.user_id
  WHERE users.role = 'siswa'
  GROUP BY users.id
");

// Ambil data siswa
$query_siswa = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa' ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembimbing</title>
    <style>
        /* ...CSS dari prompt, tidak diubah... */
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
            background: rgba(239, 68, 68, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logout-item:hover { background: rgba(239, 68, 68, 0.3); }
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
        /* Jika ingin margin pada card aktivitas terbaru */
        #dashboard .card {
            margin-top: 32px;
        }
        .card h3 { color: #2d3a4b; margin-bottom: 20px; font-size: 1.3rem; display: flex; align-items: center; }
        .card h3 .icon { margin-right: 10px; color: #4f8cff; }
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .table-header {
            background: linear-gradient(135deg, #4f8cff 0%, #3b82f6 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 { margin: 0; font-size: 1.2rem; }
        .search-box {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .search-box::placeholder { color: rgba(255, 255, 255, 0.7); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; font-weight: 600; color: #2d3a4b; }
        td { color: #64748b; }
        tr:hover { background: #f8fafc; }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-approve { background: #22c55e; color: white; }
        .btn-approve:hover { background: #16a34a; transform: translateY(-2px); }
        .btn-reject { background: #ef4444; color: white; margin-left: 5px; }
        .btn-reject:hover { background: #dc2626; transform: translateY(-2px); }
        .approved-text { color: #22c55e; font-weight: 600; }
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
            .table-container { overflow-x: auto; }
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin-top: 32px;
        }
        .stat-card {
            background: linear-gradient(135deg, #7f9cf5 0%, #a78bfa 100%);
            border-radius: 20px;
            padding: 36px 0 28px 0;
            box-shadow: 0 8px 32px rgba(79, 140, 255, 0.10);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 170px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 12px 36px rgba(79, 140, 255, 0.18);
        }
        .stat-card .icon {
            font-size: 2.8rem;
            margin-bottom: 18px;
            display: block;
        }
        .stat-card .number {
            font-size: 2.6rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(79, 140, 255, 0.10);
        }
        .stat-card .label {
            font-size: 1.1rem;
            color: #f3f4f6;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-top: 2px;
            text-align: center;
        }
        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .stat-card {
                min-height: 120px;
                padding: 28px 0 20px 0;
            }
        }
        /* Notifikasi aktivitas terbaru */
        .activity-notif {
            padding: 14px 18px;
            border-left: 5px solid #22c55e;
            background: #f0fdf4;
            margin-bottom: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 400;
        }
        .activity-notif.pending {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .activity-notif.valid {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }
        .activity-notif.presensi {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }
        .activity-notif strong {
            font-weight: 700;
        }
        .activity-notif .time {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 2px;
            display: block;
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
                <h2>👨‍🏫 Portal Pembimbing</h2>
                <p>Selamat datang, Pembimbing!</p>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active" onclick="showSection('dashboard', event)">
                    <span class="icon">🏠</span>
                    Dashboard
                </a>
                <a href="#" class="menu-item" onclick="showSection('validasi', event)">
                    <span class="icon">✅</span>
                    Validasi Aktivitas
                </a>
                <a href="#" class="menu-item" onclick="showSection('rekap', event)">
                    <span class="icon">📊</span>
                    Rekap Presensi
                </a>
                <a href="#" class="menu-item" onclick="showSection('siswa', event)">
                    <span class="icon">👥</span>
                    Data Siswa
                </a>
            </div>
            <div class="logout-item">
                <a href="#" class="menu-item" onclick="logout()">
                    <span class="icon">🚪</span>
                    Logout
                </a>
            </div>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="content-header">
                    <h1>Dashboard</h1>
                    <p>Ringkasan kegiatan siswa - <span id="currentDate"></span></p>
                </div>

                <!-- Stats Grid: Pindahkan ke atas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon">👨‍🎓</div>
                        <div class="number">
                            <?php
                            // Total siswa
                            $q_total_siswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='siswa'");
                            $total_siswa = mysqli_fetch_assoc($q_total_siswa)['total'] ?? 0;
                            echo $total_siswa;
                            ?>
                        </div>
                        <div class="label">Total Siswa</div>
                    </div>
                    <div class="stat-card">
                        <div class="icon">⏳</div>
                        <div class="number">
                            <?php
                            // Menunggu validasi
                            $q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM aktivitas WHERE status_validasi='pending'");
                            $pending = mysqli_fetch_assoc($q_pending)['total'] ?? 0;
                            echo $pending;
                            ?>
                        </div>
                        <div class="label">Menunggu Validasi</div>
                    </div>
                    <div class="stat-card">
                        <div class="icon">✅</div>
                        <div class="number">
                            <?php
                            // Sudah divalidasi khusus siswa
                            $q_valid = mysqli_query($conn, "
                                SELECT COUNT(*) as total 
                                FROM aktivitas 
                                JOIN users ON aktivitas.user_id = users.id
                                WHERE aktivitas.status_validasi='disetujui' AND users.role='siswa'
                            ");
                            $valid = mysqli_fetch_assoc($q_valid)['total'] ?? 0;
                            echo $valid;
                            ?>
                        </div>
                        <div class="label">Sudah Divalidasi</div>
                    </div>
                    <div class="stat-card">
                        <div class="icon">📈</div>
                        <div class="number">
                            <?php
                            // Tingkat kehadiran hari ini
                            $today = date('Y-m-d');
                            $q_hadir = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as hadir FROM presensi WHERE tanggal='$today'");
                            $hadir = mysqli_fetch_assoc($q_hadir)['hadir'] ?? 0;
                            $persen = $total_siswa > 0 ? round(($hadir / $total_siswa) * 100) : 0;
                            echo $persen . '%';
                            ?>
                        </div>
                        <div class="label">Tingkat Kehadiran</div>
                    </div>
                </div>
                <!-- END Stats Grid -->

                <div class="card">
                    <h3><span class="icon">🔔</span>Aktivitas Terbaru</h3>
                    <div>
                        <?php
                        $q_recent = mysqli_query($conn, "
                            SELECT aktivitas.*, users.nama 
                            FROM aktivitas 
                            JOIN users ON aktivitas.user_id = users.id
                            ORDER BY aktivitas.tanggal DESC, aktivitas.id DESC
                            LIMIT 3
                        ");
                        while ($recent = mysqli_fetch_assoc($q_recent)) :
                            // Tentukan kelas warna berdasarkan status
                            $class = 'activity-notif ';
                            if ($recent['status_validasi'] == 'pending') {
                                $class .= 'pending';
                                $status_text = 'Menunggu validasi aktivitas';
                            } elseif ($recent['status_validasi'] == 'disetujui') {
                                $class .= 'valid';
                                $status_text = 'Aktivitas telah divalidasi';
                            } else {
                                $class .= 'presensi';
                                $status_text = 'Presensi masuk tercatat';
                            }
                            // Hitung waktu relatif (opsional, sederhana)
                            $waktu = strtotime($recent['tanggal']);
                            $now = strtotime(date('Y-m-d'));
                            $selisih = ($now - $waktu) / 60; // menit
                            $time_ago = $recent['tanggal'];
                        ?>
                        <div class="<?= $class ?>">
                            <strong><?= htmlspecialchars($recent['nama']) ?></strong> - 
                            <?= htmlspecialchars($status_text) ?>
                            <span class="time"><?= htmlspecialchars($recent['tanggal']) ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <!-- Validasi Section -->
            <div id="validasi" class="content-section">
                <div class="content-header">
                    <h1>Validasi Aktivitas</h1>
                    <p>Kelola dan validasi aktivitas siswa</p>
                </div>
                <div class="table-container">
                    <div class="table-header">
                        <h3>Daftar Aktivitas Siswa</h3>
                        <input type="text" class="search-box" placeholder="Cari nama siswa..." onkeyup="searchTable()">
                    </div>
                    <table id="activitiesTable">
                        <thead>
                            <tr>
                                <th>Nama Siswa</th>
                                <th>Tanggal</th>
                                <th>Aktivitas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_assoc($query)) : ?>
                            <tr data-status="<?= $data['status_validasi'] ?>">
                                <td><?= htmlspecialchars($data['nama']) ?></td>
                                <td><?= htmlspecialchars($data['tanggal']) ?></td>
                                <td><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></td>
                                <td>
                                    <?php if ($data['status_validasi'] == 'pending') : ?>
                                        <span class="status-badge status-pending">Menunggu</span>
                                    <?php elseif ($data['status_validasi'] == 'Valid') : ?>
                                        <span class="status-badge status-approved">Disetujui</span>
                                    <?php else : ?>
                                        <span class="status-badge status-rejected"><?= htmlspecialchars($data['status_validasi']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($data['status_validasi'] == 'pending') : ?>
                                        <form method="POST" action="../proses/proses_validasi.php" style="display:inline;">
                                            <input type="hidden" name="aktivitas_id" value="<?= $data['id']; ?>">
                                            <button type="submit" name="setujui" class="btn btn-approve">Setujui</button>
                                        </form>
                                    <?php else : ?>
                                        <span class="approved-text">✅ Sudah Disetujui</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Rekap Section -->
            <div id="rekap" class="content-section">
                <div class="content-header">
                    <h1>Rekap Presensi</h1>
                    <p>Rekap kehadiran siswa per periode</p>
                </div>
                <div class="card">
                    <h3><span class="icon">📊</span>Rekap Presensi Bulanan</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($query_presensi)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['hadir']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <h3><span class="icon">📅</span>Rekap Presensi Detail</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Total Jam Kerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Ambil data presensi detail per siswa
$q_rekap = mysqli_query($conn, "
    SELECT users.nama, presensi.tanggal, presensi.jam_masuk, presensi.jam_keluar
    FROM users
    JOIN presensi ON users.id = presensi.user_id
    WHERE users.role = 'siswa'
      AND presensi.jam_masuk IS NOT NULL
      AND presensi.jam_keluar IS NOT NULL
    ORDER BY presensi.tanggal DESC, users.nama ASC
    LIMIT 50
");
                                while ($row = mysqli_fetch_assoc($q_rekap)) :
                                    $total_jam = '-';
                                    if ($row['jam_masuk'] && $row['jam_keluar']) {
                                        $start = strtotime($row['jam_masuk']);
                                        $end = strtotime($row['jam_keluar']);
                                        $diff = $end - $start;
                                        $hours = floor($diff / 3600);
                                        $minutes = floor(($diff % 3600) / 60);
                                        $total_jam = $hours . ' jam' . ($minutes > 0 ? ' ' . $minutes . ' menit' : '');
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal'] ?? '-')?></td>
                                    <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($row['jam_keluar']) : ?>
                                            <?= htmlspecialchars($row['jam_keluar']) ?>
                                        <?php else: ?>
                                            <span style="color:#d97706;">Belum Absen Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['jam_masuk'] && $row['jam_keluar']) : ?>
                                            <?= $total_jam ?>
                                        <?php else: ?>
                                            <span style="color:#64748b;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Data Siswa Section -->
           <!-- Data Siswa Section -->
<div id="siswa" class="content-section">
    <div class="content-header">
        <h1>Data Siswa</h1>
        <p>Daftar siswa yang sudah terdaftar di sistem</p>
    </div>
    <div class="card">
        <h3><span class="icon">👥</span>Daftar Siswa</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Jurusan</th>
                        <th>Kelas</th>
                    </tr>
                </thead>
                <tbody>
            
                    <?php while ($siswa = mysqli_fetch_assoc($query_siswa)) : ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($siswa['nama'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string)($siswa['username'] ?? '-')) ?></td>
<td><?= htmlspecialchars($siswa['jurusan'] !== null ? $siswa['jurusan'] : '-') ?></td>
<td><?= htmlspecialchars($siswa['kelas'] !== null ? $siswa['kelas'] : '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
        </div>
    </div>
    <script>
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
            if(event) event.target.classList.add('active');
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
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = '../auth/logout.php';
            }
        }
        // Search table by name
        function searchTable() {
            const input = document.querySelector('.search-box');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#activitiesTable tbody tr');
            rows.forEach(row => {
                const nama = row.children[0].textContent.toLowerCase();
                row.style.display = nama.includes(filter) ? '' : 'none';
            });
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
    </script>
</body>
</html>
