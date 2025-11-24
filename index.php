<?php
session_start();
include 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistem Kasir UMKM</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="pelanggan.php">Pelanggan</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Total Produk</h3>
                    <p class="number">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as total FROM produk");
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </p>
                </div>
                
                <div class="card">
                    <h3>Total Pelanggan</h3>
                    <p class="number">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as total FROM pelanggan");
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </p>
                </div>
                
                <div class="card">
                    <h3>Penjualan Hari Ini</h3>
                    <p class="number">
                        <?php
                        $today = date('Y-m-d');
                        $result = $conn->query("SELECT COUNT(*) as total FROM penjualan WHERE TanggalPenjualan = '$today'");
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </p>
                </div>
                
                <div class="card">
                    <h3>Pendapatan Hari Ini</h3>
                    <p class="number">
                        <?php
                        $result = $conn->query("SELECT SUM(TotalHarga) as total FROM penjualan WHERE TanggalPenjualan = '$today'");
                        $row = $result->fetch_assoc();
                        echo "Rp " . number_format($row['total'] ?? 0, 0, ',', '.');
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Aktivitas Terbaru</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM penjualan ORDER BY PenjualanID DESC LIMIT 5");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['PenjualanID'] . "</td>";
                            echo "<td>" . $row['TanggalPenjualan'] . "</td>";
                            echo "<td>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</td>";
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