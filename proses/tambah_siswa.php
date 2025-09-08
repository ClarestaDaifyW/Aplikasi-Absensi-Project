<?php
session_start();
include '../config/koneksi.php';

// Cegah akses langsung jika bukan pembimbing
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pembimbing') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    
    // Cek apakah username sudah ada
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: ../pembimbing/dashboard.php#akun-siswa");
        exit;
    }
    
    // Tambahkan user baru dengan role siswa
    $query = "INSERT INTO users (nama, username, password, role, jurusan, kelas) 
              VALUES ('$nama', '$username', '$password', 'siswa', '$jurusan', '$kelas')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Siswa berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . mysqli_error($conn);
    }
    
    header("Location: ../pembimbing/dashboard.php#akun-siswa");
    exit;
}
?>