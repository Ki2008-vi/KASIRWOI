<?php
session_start();
include 'config.php';

// Tambah produk
if (isset($_POST['tambah_produk'])) {
    $nama = $_POST['nama_produk'];
    // Hilangkan karakter non-digit dan non-koma/titik
    $harga = preg_replace('/[^\d.,]/', '', $_POST['harga']);
    // Ganti titik dengan kosong, koma dengan titik (jika inputan pakai format Indonesia)
    $harga = str_replace('.', '', $harga);
    $harga = str_replace(',', '.', $harga);
    $harga = floatval($harga);
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("INSERT INTO produk (NamaProduk, Harga, Stok) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $nama, $harga, $stok);
    $stmt->execute();
    header("Location: produk.php");
    exit();
}

// Hapus produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM produk WHERE ProdukID = $id");
    header("Location: produk.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manajemen Produk</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="produk.php" class="active">Produk</a></li>
                    <li><a href="pelanggan.php">Pelanggan</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="form-section">
                <h2>Tambah Produk Baru</h2>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="harga" placeholder="Harga" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="stok" placeholder="Stok" min="0" required>
                    </div>
                    <button type="submit" name="tambah_produk" class="btn-primary">Tambah Produk</button>
                </form>
            </div>

            <div class="table-section">
                <h2>Daftar Produk</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM produk");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['ProdukID'] . "</td>";
                            echo "<td>" . $row['NamaProduk'] . "</td>";
                            echo "<td>Rp " . number_format($row['Harga'], 0, ',', '.') . "</td>";
                            echo "<td>" . $row['Stok'] . "</td>";
                            echo "<td>
                                    <a href='produk.php?hapus=" . $row['ProdukID'] . "' class='btn-danger'>Hapus</a>
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