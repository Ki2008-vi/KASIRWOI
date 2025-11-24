<?php
session_start();
include 'config.php';

// Tambah pelanggan
if (isset($_POST['tambah_pelanggan'])) {
    $nama = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    
    $stmt = $conn->prepare("INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $alamat, $telepon);
    $stmt->execute();
    header("Location: pelanggan.php");
    exit();
}

// Hapus pelanggan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM pelanggan WHERE PelangganID = $id");
    header("Location: pelanggan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manajemen Pelanggan</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="pelanggan.php" class="active">Pelanggan</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="form-section">
                <h2>Tambah Pelanggan Baru</h2>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan" required>
                    </div>
                    <div class="form-group">
                        <textarea name="alamat" placeholder="Alamat" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="text" name="telepon" placeholder="Nomor Telepon" required>
                    </div>
                    <button type="submit" name="tambah_pelanggan" class="btn-primary">Tambah Pelanggan</button>
                </form>
            </div>

            <div class="table-section">
                <h2>Daftar Pelanggan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM pelanggan");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['PelangganID'] . "</td>";
                            echo "<td>" . $row['NamaPelanggan'] . "</td>";
                            echo "<td>" . $row['Alamat'] . "</td>";
                            echo "<td>" . $row['NomorTelepon'] . "</td>";
                            echo "<td>
                                    <a href='pelanggan.php?hapus=" . $row['PelangganID'] . "' class='btn-danger'>Hapus</a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>