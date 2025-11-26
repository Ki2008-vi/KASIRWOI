<?php
session_start();
include 'config.php';
// Proteksi: hanya izinkan akses jika sudah login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-top { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:18px 0; }
        .welcome h2 { margin:0; font-size:20px; }
        .muted { color:#636e72; font-size:13px; margin-top:4px }
        .quick-actions { display:flex; gap:8px; flex-wrap:wrap }
        .quick-actions .action { background:#0984e3; color:#fff; padding:8px 10px; border-radius:8px; text-decoration:none; font-size:14px }
        .quick-actions .logout { background:#d63031 }
        .secondary-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:18px }
        .panel { background:#fff; padding:14px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.06) }
        .panel ul { list-style:none; padding-left:0; margin:8px 0 0 0 }
        .panel li { padding:6px 0; border-bottom:1px dashed #eee; font-size:14px }
        .btn-success.small, .btn-info.small { padding:6px 8px; font-size:13px; border-radius:6px; text-decoration:none }
        @media (max-width:800px){ .dashboard-top{flex-direction:column;align-items:flex-start} .secondary-grid{grid-template-columns:1fr} }
    </style>
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
            <div class="dashboard-top">
                <div class="welcome">
                    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kasir'); ?>!</h2>
                    <p class="muted">Ringkasan aktivitas dan pengelolaan cepat toko Anda.</p>
                </div>
                <div class="quick-actions">
                    <a href="transaksi.php" class="action">Buka Kasir</a>
                    <a href="produk.php" class="action">Kelola Produk</a>
                    <a href="pelanggan.php" class="action">Pelanggan</a>
                    <a href="laporan.php" class="action">Laporan</a>
                    <a href="logout.php" class="action logout">Logout</a>
                </div>
            </div>

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

            <div class="secondary-grid">
                <div class="panel">
                    <h3>Produk Terlaris</h3>
                    <ul>
                        <?php
                        $resTop = $conn->query("SELECT p.NamaProduk, SUM(d.JumlahProduk) as terjual FROM detailpenjualan d JOIN produk p ON d.ProdukID = p.ProdukID GROUP BY d.ProdukID ORDER BY terjual DESC LIMIT 5");
                        if ($resTop && $resTop->num_rows > 0) {
                            while ($r = $resTop->fetch_assoc()) {
                                echo '<li>' . htmlspecialchars($r['NamaProduk']) . ' — <b>' . intval($r['terjual']) . '</b> pcs</li>';
                            }
                        } else {
                            echo '<li>Tidak ada data penjualan.</li>';
                        }
                        ?>
                    </ul>
                </div>

                <div class="panel">
                    <h3>Stok Rendah</h3>
                    <ul>
                        <?php
                        $resLow = $conn->query("SELECT NamaProduk, Stok FROM produk WHERE Stok <= 5 ORDER BY Stok ASC LIMIT 5");
                        if ($resLow && $resLow->num_rows > 0) {
                            while ($l = $resLow->fetch_assoc()) {
                                echo '<li>' . htmlspecialchars($l['NamaProduk']) . ' — <span style="color:#d63031;">' . intval($l['Stok']) . '</span> pcs</li>';
                            }
                        } else {
                            echo '<li>Semua stok cukup.</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Aktivitas Terbaru</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT p.PenjualanID, p.TanggalPenjualan, p.TotalHarga, pl.NamaPelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID ORDER BY p.PenjualanID DESC LIMIT 8");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['PenjualanID'] . "</td>";
                            echo "<td>" . $row['TanggalPenjualan'] . "</td>";
                            echo "<td>" . ($row['NamaPelanggan'] ?: 'Umum') . "</td>";
                            echo "<td>Rp " . number_format($row['TotalHarga'], 0, ',', '.') . "</td>";
                            echo "<td><a href='nota.php?id=" . $row['PenjualanID'] . "' class='btn-success small'>Nota</a> <a href='detail_transaksi.php?id=" . $row['PenjualanID'] . "' class='btn-info small' style='margin-left:6px;'>Detail</a></td>";
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
