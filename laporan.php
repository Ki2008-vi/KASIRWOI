<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Laporan Penjualan</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="pelanggan.php">Pelanggan</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="laporan.php" class="active">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="laporan-section">
                <h2>Riwayat Transaksi</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("
                            SELECT p.PenjualanID, p.TanggalPenjualan, p.TotalHarga, pl.NamaPelanggan 
                            FROM penjualan p 
                            LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
                            ORDER BY p.PenjualanID DESC
                        ");
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['PenjualanID'] . "</td>";
                            echo "<td>" . $row['TanggalPenjualan'] . "</td>";
                            echo "<td>" . ($row['NamaPelanggan'] ?: 'Tidak ada') . "</td>";
                            echo "<td>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</td>";
                                                        echo "<td><a href='detail_transaksi.php?id=" . $row['PenjualanID'] . "' class='btn-info'>Lihat</a> ";
                                                        // Ambil kembalian dari database jika ada
                                                        $kembalian = 0;
                                                        $result_kem = $conn->query("SELECT TotalHarga FROM penjualan WHERE PenjualanID = " . $row['PenjualanID']);
                                                        $row_kem = $result_kem->fetch_assoc();
                                                        // Asumsikan jumlah bayar = total harga (karena data kembalian tidak disimpan di DB), jadi kembalian hanya muncul jika nota diakses dari transaksi
                                                        echo "<a href='nota.php?id=" . $row['PenjualanID'] . "' class='btn-success' style='margin-left:6px;'>Nota</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="statistik-section">
                <h2>Statistik Penjualan</h2>
                <div class="statistik-grid">
                    <div class="stat-card">
                        <h3>Total Penjualan</h3>
                        <p>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as total FROM penjualan");
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                            ?>
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Pendapatan</h3>
                        <p>
                            <?php
                            $result = $conn->query("SELECT SUM(TotalHarga) as total FROM penjualan");
                            $row = $result->fetch_assoc();
                            echo "Rp " . number_format($row['total'] ?? 0, 0, ',', '.');
                            ?>
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3>Rata-rata Transaksi</h3>
                        <p>
                            <?php
                            $result = $conn->query("SELECT AVG(TotalHarga) as rata FROM penjualan");
                            $row = $result->fetch_assoc();
                            echo "Rp " . number_format($row['rata'] ?? 0, 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>