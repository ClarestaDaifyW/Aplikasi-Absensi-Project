<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $tanggal = date("Y-m-d");
  $deskripsi = $_POST['deskripsi'];

  mysqli_query($conn, "INSERT INTO aktivitas (user_id, tanggal, deskripsi, status_validasi)
      VALUES ('$user_id', '$tanggal', '$deskripsi', 'pending')");

  header("Location: dashboard.php");
}
?>

<h3>Isi Aktivitas Harian</h3>
<form method="POST">
  Deskripsi Aktivitas:<br>
  <textarea name="deskripsi" rows="5" cols="40" required></textarea><br>
  <button type="submit">Simpan</button>
</form>
