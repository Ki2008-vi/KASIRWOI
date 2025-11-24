<?php
// koneksi.php

$host = "localhost"; // Ganti jika host Anda berbeda
$user = "root";      // Ganti dengan username database Anda
$password = "";      // Ganti dengan password database Anda
$database = "kasir_db";

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    // Memberikan pesan error yang lebih aman untuk lingkungan produksi
    die("Koneksi gagal: " . $conn->connect_error);
}

// Opsional: Mengatur karakter set ke utf8mb4
$conn->set_charset("utf8mb4");

// Contoh penggunaan:
// $sql = "SELECT * FROM produk";
// $result = $conn->query($sql);

?>