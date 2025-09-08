<<<<<<< HEAD
=======
<?php
session_start();
include "../config/koneksi.php";
// koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role     = $_POST['role'];
    $kelas    = $_POST['kelas'] ?? null;
    $jurusan  = $_POST['jurusan'] ?? null;

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

  if ($check->num_rows > 0) {
    $_SESSION['register_error'] = "Username sudah dipakai, coba yang lain.";
    header("Location: register.php");
    exit();
} else {
        // Insert user baru
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama, kelas, jurusan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $password, $role, $nama, $kelas, $jurusan);

        if ($stmt->execute()) {
            $_SESSION['register_success'] = "Akun telah berhasil dibuat, silakan masuk.";
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
    }
?>

>>>>>>> main
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
            animation: slideUp 0.7s cubic-bezier(.39,.58,.57,1) both;
        }

        @keyframes slideUp {
    0% {
        opacity: 0;
        transform: translateY(40px) scale(0.98);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
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

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            .register-container h2 {
                font-size: 24px;
            }
        }

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

        /* Styling untuk ikon mata */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-size: contain;
            background-repeat: no-repeat;
            cursor: pointer;
            z-index: 2;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        .password-toggle:hover {
            opacity: 1;
        }

        .password-toggle.show {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'/%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'/%3E%3C/svg%3E");
        }

        .password-toggle.hide {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21'/%3E%3C/svg%3E");
        }

        .form-group input,
        .form-group select {
            padding-left: 50px;
        }

        .form-group.password input {
            padding-left: 50px;
            padding-right: 50px; /* Memberi ruang untuk ikon mata */
        }

        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-box {
            background: #fff;
            padding: 32px 28px 24px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44,62,80,0.18);
            text-align: center;
            max-width: 340px;
            width: 100%;
            position: relative;
            animation: slideInDown 0.4s;
        }
        .modal-box p {
            font-size: 17px;
            color: #dc2626;
            margin-bottom: 18px;
            font-weight: 500;
        }
        .modal-link {
            display: inline-block;
            color: #ffffff;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-top: 12px;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border: none;
            cursor: pointer;
            min-width: 140px;
        }

        .modal-link:hover {
            background: linear-gradient(135deg, #5a0bb5 0%, #1e5de6 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
            color: #ffffff;
        }

        .modal-link:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(255, 253, 253, 0.52);
        }

        .modal-link:focus {
            outline: 2px solid #ffffff;
            outline-offset: 2px;
        }

        .modal-content {
            color: #333333;
            font-weight: 500;
            line-height: 1.5;
        }

        .modal-content .error-message {
            color: #dc3545;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .modal-link.high-contrast {
            background: #2563eb;
            color: #ffffff;
            border: 2px solid #1d4ed8;
        }

        .modal-link.high-contrast:hover {
            background: #1d4ed8;
            border-color: #1e40af;
            color: #ffffff;
        }
        .modal-close {
            position: absolute;
            top: 10px; right: 16px;
            font-size: 22px;
            color: #888;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #dc2626;
        }

        /* Tambahan untuk notifikasi sukses yang lebih baik */
        .success-notification {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .success-notification::before {
            content: '';
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }
        
        .success-notification a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
            margin-left: 5px;
        }

    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registrasi Akun</h2>
        
        <?php if (isset($_SESSION['register_success'])): ?>
        <div class="success-notification">
            <?= htmlspecialchars($_SESSION['register_success']); ?>
            <a href="login.php">Masuk Sekarang</a>
        </div>
        <?php unset($_SESSION['register_success']); endif; ?>
        
        <?php if (isset($_SESSION['register_error'])): ?>
        <div class="error-message notification">
            <?= htmlspecialchars($_SESSION['register_error']); ?>
        </div>
        <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group name">
                <input name="nama" placeholder="Nama Lengkap" required>
            </div>

                <?php if (isset($_SESSION['register_success'])): ?>
    <div class="success-notification" style="margin-bottom: 12px;">
        <?= htmlspecialchars($_SESSION['register_success']); ?>
        <a href="login.php">Masuk Sekarang</a>
    </div>
    <?php unset($_SESSION['register_success']); endif; ?>
            
            <div class="form-group username">
                <input name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group password">
                <input name="password" type="password" placeholder="Password" required id="password">
                <span class="password-toggle show" onclick="togglePassword()"></span>
            </div>
            
            <div class="form-group role">
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
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('show');
                toggleIcon.classList.add('hide');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('hide');
                toggleIcon.classList.add('show');
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

        function closeModal() {
            document.getElementById('modalError').style.display = 'none';    
        }

        
    </script>
</body>
</html>