<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: laporan.php");
    exit();
}

$id = $_GET['id'];

// Ambil data transaksi
$result = $conn->query("
    SELECT p.*, pl.NamaPelanggan 
    FROM penjualan p 
    LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
    WHERE p.PenjualanID = $id
");
$transaksi = $result->fetch_assoc();

// Ambil detail transaksi
$detail_result = $conn->query("
    SELECT d.*, pr.NamaProduk 
    FROM detailpenjualan d 
    JOIN produk pr ON d.ProdukID = pr.ProdukID 
    WHERE d.PenjualanID = $id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Detail Transaksi #<?php echo $id; ?></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="pelanggan.php">Pelanggan</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="detail-transaksi">
                <div class="info-transaksi">
                    <h2>Informasi Transaksi</h2>
                    <p><strong>ID Transaksi:</strong> #<?php echo $transaksi['PenjualanID']; ?></p>
                    <p><strong>Tanggal:</strong> <?php echo $transaksi['TanggalPenjualan']; ?></p>
                    <p><strong>Pelanggan:</strong> <?php echo $transaksi['NamaPelanggan'] ?: 'Tidak ada'; ?></p>
                    <p><strong>Total Harga:</strong> Rp <?php echo number_format($transaksi['TotalHarga'], 0, ',', '.'); ?></p>
                </div>

                <div class="detail-items">
                    <h2>Item yang Dibeli</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $detail_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['NamaProduk'] . "</td>";
                                echo "<td>" . $row['JumlahProduk'] . "</td>";
                                echo "<td>Rp " . number_format($row['Subtotal'] / $row['JumlahProduk'], 0, ',', '.') . "</td>";
                                echo "<td>Rp " . number_format($row['Subtotal'], 0, ',', '.') . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <a href="laporan.php" class="btn-primary">Kembali ke Laporan</a>
            </div>
        </main>
    </div>
</body>
</html>