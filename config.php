<?php
$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "kasir_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>