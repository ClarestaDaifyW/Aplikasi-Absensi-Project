<?php
session_start();
include '../config/koneksi.php';

// Cek jika user pembimbing
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pembimbing') {
  header("Location: ../auth/login.php");
  exit;
}

// Cek jika tombol validasi ditekan
if (isset($_POST['setujui'])) {
  $aktivitas_id = $_POST['aktivitas_id'];

  // Update status jadi disetujui
  $update = mysqli_query($koneksi, "UPDATE aktivitas SET status_validasi='disetujui' WHERE id='$aktivitas_id'");

  header("Location: ../pembimbing/dashboard.php");
}
?>
