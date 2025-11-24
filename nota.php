<?php
session_start();
include 'config.php';

// Cek apakah ada data nota
if (!isset($_SESSION['nota_data']) && !isset($_GET['id'])) {
    die("Tidak ada data transaksi untuk ditampilkan.");
}

// Jika ada parameter ID, ambil data dari database
if (isset($_GET['id'])) {
    $penjualan_id = $_GET['id'];
    
    // Ambil data penjualan
    $result = $conn->query("
        SELECT p.*, pl.NamaPelanggan, pl.Alamat, pl.NomorTelepon 
        FROM penjualan p 
        LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID 
        WHERE p.PenjualanID = $penjualan_id
    ");
    $transaksi = $result->fetch_assoc();
    
    // Ambil detail penjualan
    $detail_result = $conn->query("
        SELECT d.*, pr.NamaProduk 
        FROM detailpenjualan d 
        JOIN produk pr ON d.ProdukID = pr.ProdukID 
        WHERE d.PenjualanID = $penjualan_id
    ");
    
    $nota_data = [
        'penjualan_id' => $penjualan_id,
        'tanggal' => $transaksi['TanggalPenjualan'],
        'total_harga' => $transaksi['TotalHarga'],
        'pelanggan' => [
            'NamaPelanggan' => $transaksi['NamaPelanggan'] ?: 'Pelanggan Umum',
            'Alamat' => $transaksi['Alamat'] ?: '-',
            'NomorTelepon' => $transaksi['NomorTelepon'] ?: '-'
        ],
        'items' => []
    ];
    
    while ($row = $detail_result->fetch_assoc()) {
        $nota_data['items'][] = [
            'nama' => $row['NamaProduk'],
            'harga' => $row['Subtotal'] / $row['JumlahProduk'],
            'jumlah' => $row['JumlahProduk'],
            'subtotal' => $row['Subtotal']
        ];
    }
} else {
    // Ambil data dari session
    $nota_data = $_SESSION['nota_data'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran - Toko UMKM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/stylenota.css">
</head>
<body>
    <div class="nota-container">
        <div class="nota-header">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Cetak Nota
            </button>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Kasir
            </a>
        </div>

        <div class="nota-content" id="notaContent">
            <!-- Header Toko -->
            <div class="toko-header">
                <h1>TOKO UMKM MAKMUR</h1>
                <p>Jl. Contoh No. 123, Kota Contoh</p>
                <p>Telp: (021) 123-4567 | Email: info@tokoumkm.com</p>
                <div class="divider"></div>
            </div>

            <!-- Info Transaksi -->
            <div class="transaksi-info">
                <div class="info-row">
                    <span>No. Transaksi:</span>
                    <span>#<?php echo str_pad($nota_data['penjualan_id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span>Tanggal:</span>
                    <span><?php echo date('d/m/Y H:i:s', strtotime($nota_data['tanggal'])); ?></span>
                </div>
                <div class="info-row">
                    <span>Kasir:</span>
                    <span>System</span>
                </div>
            </div>

            <!-- Info Pelanggan -->
            <div class="pelanggan-info">
                <h3>Data Pelanggan</h3>
                <div class="info-row">
                    <span>Nama:</span>
                    <span><?php echo $nota_data['pelanggan']['NamaPelanggan']; ?></span>
                </div>
                <?php if (isset($nota_data['pelanggan']['Alamat'])): ?>
                <div class="info-row">
                    <span>Alamat:</span>
                    <span><?php echo $nota_data['pelanggan']['Alamat']; ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($nota_data['pelanggan']['NomorTelepon'])): ?>
                <div class="info-row">
                    <span>Telepon:</span>
                    <span><?php echo $nota_data['pelanggan']['NomorTelepon']; ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Daftar Barang -->
            <div class="items-section">
                <h3>Detail Pembelian</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $total_item = 0;
                        foreach ($nota_data['items'] as $item) {
                            $total_item += $item['jumlah'];
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo $item['nama']; ?></td>
                                <td class="text-center"><?php echo $item['jumlah']; ?></td>
                                <td class="text-right">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary-section">
                <div class="summary-row">
                    <span>Total Item:</span>
                    <span><?php echo $total_item; ?> item</span>
                </div>
                <div class="summary-row total">
                    <span>Total Pembayaran:</span>
                    <span>Rp <?php echo number_format($nota_data['total_harga'], 0, ',', '.'); ?></span>
                </div>
                <?php 
                $jumlah_bayar = isset($_GET['jumlah_bayar']) ? floatval($_GET['jumlah_bayar']) : null;
                $kembalian = isset($_GET['kembalian']) ? floatval($_GET['kembalian']) : null;
                // Estimasi jumlah bayar jika tidak ada di URL (asumsi pembayaran pas)
                if ($jumlah_bayar === null) {
                    $jumlah_bayar = $nota_data['total_harga'];
                }
                // Estimasi kembalian jika tidak ada di URL
                if ($kembalian === null) {
                    $kembalian = $jumlah_bayar - $nota_data['total_harga'];
                }
                if ($jumlah_bayar !== null) {
                ?>
                <div class="summary-row">
                    <span>Jumlah Bayar:</span>
                    <span>Rp <?php echo number_format($jumlah_bayar, 0, ',', '.'); ?></span>
                </div>
                <?php }
                if ($kembalian > 0) {
                ?>
                <div class="summary-row" style="color:#00b894;font-weight:bold;">
                    <span>Kembalian:</span>
                    <span>Rp <?php echo number_format($kembalian, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row" style="color:#636e72;"><span>Keterangan:</span><span>Pelanggan membayar lebih, kembalian diberikan.</span></div>
                <?php } elseif ($kembalian == 0) { ?>
                <div class="summary-row" style="color:#0984e3;font-weight:bold;"><span>Keterangan:</span><span>Pembayaran pas</span></div>
                <?php } else { ?>
                <div class="summary-row" style="color:#d63031;font-weight:bold;"><span>Keterangan:</span><span>Pembayaran kurang</span></div>
                <?php }
                ?>
                <?php if (isset($nota_data['metode_bayar'])): ?>
                <div class="summary-row">
                    <span>Metode Bayar:</span>
                    <span class="payment-method"><?php echo strtoupper($nota_data['metode_bayar']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="nota-footer">
                <div class="divider"></div>
                <p class="thank-you">Terima kasih atas kunjungan Anda</p>
                <p class="footer-note">Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan</p>
                
                <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Transaksi berhasil diproses!</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto print jika transaksi baru
        <?php if (isset($_GET['success'])): ?>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        <?php endif; ?>

        // Keyboard shortcut untuk print
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>