<?php
session_start();
include '../config/koneksi.php';

$pesan = '';
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $sql = "SELECT * FROM users WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Username atau password salah!";
        } else {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Simpan hanya id dan role ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                // Redirect sesuai role
                if ($user['role'] == 'pembimbing') {
                    header("Location: ../pembimbing/dashboard.php");
                } else {
                    header("Location: ../siswa/dashboard.php");
                }
                exit;
            } else {
                $error = "Username atau password salah!";
            }
        }
    }
}
// ...existing code...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Absensi</title>
    <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-container {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
    }

    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea, #764ba2);
      border-radius: 12px 12px 0 0;
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
      font-size: 28px;
      font-weight: 600;
    }

    .pesan-error {
      background: #fee;
      color: #c33;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #fcc;
      font-size: 14px;
      text-align: center;
    }

    .pesan-sukses {
      background: #efe;
      color: #363;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #cfc;
      font-size: 14px;
      text-align: center;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    input[type="text"], 
    input[type="password"],
    input[name="username"],
    input[name="password"] {
      width: 100%;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      transition: all 0.3s ease;
      background: #fff;
      box-sizing: border-box;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }

    input[type="text"]:focus, 
    input[type="password"]:focus,
    input[name="username"]:focus,
    input[name="password"]:focus {
      outline: none;
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }

    input[type="text"]::placeholder, 
    input[type="password"]::placeholder,
    input[name="username"]::placeholder,
    input[name="password"]::placeholder {
      color: #999;
    }

    button {
      padding: 15px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    button:active {
      transform: translateY(0);
    }

    p {
      text-align: center;
      margin-top: 25px;
      color: #666;
      font-size: 14px;
    }

    p a {
      color: #667eea;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    p a:hover {
      color: #764ba2;
      text-decoration: underline;
    }

    /* Password container for eye toggle */
    .password-container {
      position: relative;
      width: 100%;
    }

    .password-container input {
      width: 100%;
      padding-right: 45px;
    }

    /* Eye toggle icon hover effect */
    #toggleIcon {
      transition: opacity 0.3s ease;
    }

    #toggleIcon:hover {
      opacity: 0.7;
    }

    /* Responsive design */
    @media (max-width: 480px) {
      .login-container {
        padding: 30px 20px;
        margin: 10px;
      }
      
      h2 {
        font-size: 24px;
      }
      
      input[type="text"], 
      input[type="password"],
      input[name="username"],
      input[name="password"] {
        padding: 12px;
        font-size: 14px;
      }
      
      button {
        padding: 12px;
        font-size: 14px;
      }
    }

    /* Loading animation for button */
    button:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    /* Smooth animations */
    .login-container {
      animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    </style>
</head>
<body>
<div class="login-container">
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <div class="pesan-error"><?= $error ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="pesan-sukses"><?= $success ?></div>
  <?php endif; ?>
  <form method="POST">
    <input name="username" placeholder="Username" required>
    <div style="position:relative;">
      <input id="pw" name="password" type="password" placeholder="Password" required style="padding-right:38px;">
      <span onclick="togglePw()" id="toggleIcon" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;">
        <!-- Mata terbuka (default) -->
        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#888" stroke-width="2" d="M1.5 12S5.5 5.5 12 5.5 22.5 12 22.5 12 18.5 18.5 12 18.5 1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3.5" stroke="#888" stroke-width="2"/></svg>
        <!-- Mata terpejam (hidden) -->
        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" style="display:none;"><path stroke="#888" stroke-width="2" d="M3 3l18 18M1.5 12S5.5 5.5 12 5.5c2.2 0 4.1.6 5.7 1.5M22.5 12S18.5 18.5 12 18.5c-2.2 0-4.1-.6-5.7-1.5"/><circle cx="12" cy="12" r="3.5" stroke="#888" stroke-width="2"/></svg>
      </span>
    </div>
    <button name="login">Login</button>
  </form>
  <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('pw');
  const eyeOpen = document.getElementById('eyeOpen');
  const eyeClosed = document.getElementById('eyeClosed');
  if (pw.type === "password") {
    pw.type = "text";
    eyeOpen.style.display = "none";
    eyeClosed.style.display = "inline";
  } else {
    pw.type = "password";
    eyeOpen.style.display = "inline";
    eyeClosed.style.display = "none";
  }
}
</script>
</body>
</html>