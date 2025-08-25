<?php
$koneksi = mysqli_connect("localhost", "root", "", "magang_edusoft");
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
