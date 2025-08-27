<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    error_log(print_r($_POST, true));

    // Validasi field tidak boleh kosong
    if (empty($nama) || empty($username) || empty($password) || empty($role)) {
        echo "<script>alert('Semua field harus diisi!'); window.location.href='register.php';</script>";
        exit();
    }

    // Validasi panjang password minimal
    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter!'); window.location.href='register.php';</script>";
        exit();
    }

    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "magang_edusoft");

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Set charset untuk menghindari masalah encoding
    $conn->set_charset("utf8");

    try {
        // Cek apakah username sudah ada
        $check = $conn->prepare("SELECT username FROM users WHERE username=?");
        if (!$check) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Username sudah terdaftar!'); window.location.href='register.php';</script>";
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert data ke database
// Pastikan query INSERT sesuai dengan struktur tabel
$stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssss", $nama, $username, $hashed_password, $role);

        if ($stmt->execute()) {
            // Berhasil registrasi
            session_start();
            $_SESSION['register_success'] = "Registrasi berhasil! Silakan login.";
            
            // Debug: Cek apakah data benar-benar masuk
            $user_id = $conn->insert_id;
            error_log("User berhasil terdaftar dengan ID: " . $user_id);
            
            header("Location: login.php");
            exit();
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

    } catch (Exception $e) {
        error_log("Error dalam registrasi: " . $e->getMessage());
        echo "<script>alert('Registrasi gagal: " . $e->getMessage() . "'); window.location.href='register.php';</script>";
        exit();
    } finally {
        // Tutup statement dan koneksi
        if (isset($check)) $check->close();
        if (isset($stmt)) $stmt->close();
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            max-width: 420px;
            width: 100%;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2d3a4b;
            font-size: 28px;
            font-weight: 600;
            position: relative;
        }

        .register-container h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            color: #2d3a4b;
        }

        .register-container input:focus,
        .register-container select:focus {
            border-color: #667eea;
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .register-container input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .register-container select {
            cursor: pointer;
        }

        .register-container select option {
            padding: 10px;
            background: #fff;
            color: #2d3a4b;
        }

        .register-container button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .register-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .register-container button:active {
            transform: translateY(0);
        }

        .register-container p {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #6b7280;
        }

        .register-container a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-container a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
            border-left: 4px solid #047857;
            animation: slideInDown 0.5s ease-out;
        }

        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
            border-left: 4px solid #b91c1c;
            animation: slideInDown 0.5s ease-out;
        }

        .info-message {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
            border-left: 4px solid #1e40af;
            animation: slideInDown 0.5s ease-out;
        }

        .notification {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification::before {
            content: '';
            width: 20px;
            height: 20px;
            background-size: contain;
            background-repeat: no-repeat;
            flex-shrink: 0;
        }

        .success-message.notification::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E");
        }

        .error-message.notification::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E");
        }

        .info-message.notification::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E");
        }

        .notification a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .notification a:hover {
            opacity: 0.8;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Close button for notifications */
        .notification-close {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .notification-close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .register-container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            .register-container h2 {
                font-size: 24px;
            }
        }

        /* Loading animation for button */
        .loading {
            position: relative;
            color: transparent;
        }

        .loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Input icons */
        .form-group {
            position: relative;
        }

        .form-group::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-size: contain;
            background-repeat: no-repeat;
            z-index: 1;
            opacity: 0.5;
        }

        .form-group.name::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/%3E%3C/svg%3E");
        }

        .form-group.username::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m6 0H9m6 0a2 2 0 012 2m0 0a2 2 0 01-2 2H9a2 2 0 01-2-2m0 0a2 2 0 012-2h6a2 2 0 012 2z'/%3E%3C/svg%3E");
        }

        .form-group.password::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'/%3E%3C/svg%3E");
        }

        /* Password toggle eye icon */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 22px;
            height: 22px;
            color: #9ca3af;
            transition: color 0.3s ease;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .password-toggle svg {
            width: 100%;
            height: 100%;
            stroke: currentColor;
        }

        /* Adjust padding for password input to make room for eye icon */
        .form-group.password input {
            padding-right: 55px;
        }

        .form-group.role::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'/%3E%3C/svg%3E");
        }

        /* Custom Dropdown Styles */
        .custom-dropdown {
            position: relative;
            width: 100%;
        }

        .dropdown-selected {
            width: 100%;
            padding: 15px 50px 15px 50px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            cursor: pointer;
            color: #9ca3af;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .dropdown-selected.active {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
            color: #2d3a4b;
        }

        .dropdown-selected.has-value {
            color: #2d3a4b;
        }

        .dropdown-arrow {
            width: 20px;
            height: 20px;
            color: #9ca3af;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .dropdown-selected.active .dropdown-arrow {
            transform: rotate(180deg);
            color: #667eea;
        }

        .dropdown-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 2px solid #667eea;
            border-top: none;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .dropdown-options.show {
            max-height: 200px;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-option {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #2d3a4b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dropdown-option:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .dropdown-option:first-child {
            border-top: 1px solid #e1e5e9;
        }

        .dropdown-option:last-child {
            border-radius: 0 0 10px 10px;
        }

        .dropdown-option-icon {
            width: 20px;
            height: 20px;
            opacity: 0.7;
        }

        .dropdown-option:hover .dropdown-option-icon {
            opacity: 1;
        }

        
        /* .form-group.role select {
            display: none;
        } */

        .form-group input,
        .form-group select {
            padding-left: 50px;
        }

        /* Specific padding for password input */
        .form-group.password input {
            padding-left: 50px;
            padding-right: 55px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registrasi Akun</h2>
        
        <!-- Example notifications - uncomment and use in your PHP -->
        <!-- Success Message -->
        <!-- <div class="success-message notification">
            <span>Registrasi berhasil! Silakan <a href="login.php">login</a>.</span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div> -->
        
        <!-- Error Message -->
        <!-- <div class="error-message notification">
            <span>Username sudah terdaftar!</span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div> -->
        
        <!-- Info Message -->
        <!-- <div class="info-message notification">
            <span>Registrasi gagal!</span>
            <button class="notification-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div> -->
        
<form method="POST" action="register.php">
    <div class="form-group name">
        <input name="nama" placeholder="Nama Lengkap" required>
    </div>
    
    <div class="form-group username">
        <input name="username" placeholder="Username" required>
    </div>
    
    <div class="form-group password">
        <input name="password" type="password" placeholder="Password" required id="password">
        <span class="password-toggle" onclick="togglePassword()">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </span>
    </div>
    
    <div class="form-group role">
        <!-- Gunakan select biasa, bukan custom dropdown untuk memastikan data terkirim -->
        <select name="role" required id="roleSelect">
            <option value="">Pilih Role</option>
            <option value="siswa">Siswa</option>
            <option value="pembimbing">Pembimbing</option>
        </select>
    </div>
    
    <button name="register" type="submit">Register</button>
</form>
        
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>

    <script>
        // Custom dropdown functionality
        function toggleDropdown() {
            const dropdownOptions = document.getElementById('dropdownOptions');
            const selectedElement = document.querySelector('.dropdown-selected');
            
            dropdownOptions.classList.toggle('show');
            selectedElement.classList.toggle('active');
        }

        function selectRole(value) {
            const selectedRole = document.getElementById('selectedRole');
            const roleSelect = document.getElementById('roleSelect');
            const selectedElement = document.querySelector('.dropdown-selected');
            
            // Update display text
            selectedRole.textContent = value === 'siswa' ? 'Siswa' : 'Pembimbing';
            
            // Update hidden select value
            roleSelect.value = value;
            
            // Add has-value class for styling
            selectedElement.classList.add('has-value');
            
            // Close dropdown
            document.getElementById('dropdownOptions').classList.remove('show');
            selectedElement.classList.remove('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-dropdown')) {
                document.getElementById('dropdownOptions').classList.remove('show');
                document.querySelector('.dropdown-selected').classList.remove('active');
            }
        });

        
}

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Add loading animation on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = document.querySelector('button[name="register"]');
            button.classList.add('loading');
            button.disabled = true;
        });

        // Add smooth focus transitions
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>