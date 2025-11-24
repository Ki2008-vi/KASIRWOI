<?php
session_start();
include 'config.php';

// Inisialisasi keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Tambah produk ke keranjang
if (isset($_POST['tambah_ke_keranjang'])) {
    $produk_id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah'];
    
    $result = $conn->query("SELECT * FROM produk WHERE ProdukID = $produk_id");
    $produk = $result->fetch_assoc();
    
    if ($produk && $produk['Stok'] >= $jumlah) {
        if (isset($_SESSION['keranjang'][$produk_id])) {
            $_SESSION['keranjang'][$produk_id]['jumlah'] += $jumlah;
        } else {
            $_SESSION['keranjang'][$produk_id] = [
                'nama' => $produk['NamaProduk'],
                'harga' => $produk['Harga'],
                'jumlah' => $jumlah
            ];
        }
        
        // Kurangi stok
        $conn->query("UPDATE produk SET Stok = Stok - $jumlah WHERE ProdukID = $produk_id");
    }
}

// Hapus item dari keranjang
if (isset($_GET['hapus_item'])) {
    $produk_id = $_GET['hapus_item'];
    $jumlah = $_SESSION['keranjang'][$produk_id]['jumlah'];
    
    // Kembalikan stok
    $conn->query("UPDATE produk SET Stok = Stok + $jumlah WHERE ProdukID = $produk_id");
    
    unset($_SESSION['keranjang'][$produk_id]);
    header("Location: transaksi.php");
    exit();
}

// Proses transaksi
if (isset($_POST['proses_transaksi'])) {
    $pelanggan_id = $_POST['pelanggan_id'];
    $jumlah_bayar = isset($_POST['jumlah_bayar']) ? floatval($_POST['jumlah_bayar']) : 0;
    $total_harga = 0;
    foreach ($_SESSION['keranjang'] as $item) {
        $total_harga += $item['harga'] * $item['jumlah'];
    }
    $status_bayar = '';
    $kembalian = 0;
    if ($jumlah_bayar < $total_harga) {
        $status_bayar = '<span style="color:#d63031;font-weight:bold;">Kurang bayar: Rp ' . number_format($total_harga - $jumlah_bayar, 0, ',', '.') . '</span>';
    } elseif ($jumlah_bayar > $total_harga) {
        $kembalian = $jumlah_bayar - $total_harga;
        $status_bayar = '<span style="color:#00b894;font-weight:bold;">Kembalian: Rp ' . number_format($kembalian, 0, ',', '.') . '</span>';
    } else {
        $status_bayar = '<span style="color:#0984e3;font-weight:bold;">Pembayaran pas</span>';
    }
    // Jika kurang bayar, jangan proses transaksi
    if ($jumlah_bayar < $total_harga) {
        echo '<script>alert("Jumlah bayar kurang! Silakan masukkan jumlah yang cukup.");</script>';
    } else {
        // Simpan penjualan
        $stmt = $conn->prepare("INSERT INTO penjualan (TanggalPenjualan, TotalHarga, PelangganID) VALUES (CURDATE(), ?, ?)");
        $stmt->bind_param("di", $total_harga, $pelanggan_id);
        $stmt->execute();
        $penjualan_id = $conn->insert_id;
        
        // Simpan detail penjualan
        foreach ($_SESSION['keranjang'] as $produk_id => $item) {
            $subtotal = $item['harga'] * $item['jumlah'];
            $stmt = $conn->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $penjualan_id, $produk_id, $item['jumlah'], $subtotal);
            $stmt->execute();
        }
        
        // Kosongkan keranjang
        $_SESSION['keranjang'] = [];
        // Redirect ke nota, kirim info kembalian
        header("Location: nota.php?id=$penjualan_id&success=1&kembalian=$kembalian");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - POS UMKM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Transaksi Penjualan</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="pelanggan.php">Pelanggan</a></li>
                    <li><a href="transaksi.php" class="active">Transaksi</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <div class="transaksi-grid">
                <div class="produk-section">
                    <h2>Pilih Produk</h2>
                    <div class="produk-list">
                        <?php
                        $result = $conn->query("SELECT * FROM produk WHERE Stok > 0");
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='produk-item'>";
                            echo "<h3>" . $row['NamaProduk'] . "</h3>";
                            echo "<p>Harga: Rp " . number_format($row['Harga'], 0, ',', '.') . "</p>";
                            echo "<p>Stok: " . $row['Stok'] . "</p>";
                            echo "<form method='POST' class='produk-form'>";
                            echo "<input type='hidden' name='produk_id' value='" . $row['ProdukID'] . "'>";
                            echo "<input type='number' name='jumlah' value='1' min='1' max='" . $row['Stok'] . "'>";
                            echo "<button type='submit' name='tambah_ke_keranjang' class='btn-primary'>Tambah</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="keranjang-section">
                    <h2>Keranjang Belanja</h2>
                    <div class="keranjang-items">
                        <?php
                        $total = 0;
                        foreach ($_SESSION['keranjang'] as $produk_id => $item) {
                            $subtotal = $item['harga'] * $item['jumlah'];
                            $total += $subtotal;
                            
                            echo "<div class='keranjang-item'>";
                            echo "<h4>" . $item['nama'] . "</h4>";
                            echo "<p>Rp " . number_format($item['harga'], 0, ',', '.') . " x " . $item['jumlah'] . " = Rp " . number_format($subtotal, 0, ',', '.') . "</p>";
                            echo "<a href='transaksi.php?hapus_item=$produk_id' class='btn-danger'>Hapus</a>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    
                    <div class="total-section">
                        <h3>Total: Rp <?php echo number_format($total, 0, ',', '.'); ?></h3>
                    </div>
                    
                    <form method="POST" class="checkout-form">
                        <div class="form-group">
                            <label for="pelanggan_id">Pilih Pelanggan:</label>
                            <select name="pelanggan_id" id="pelanggan_id" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                <?php
                                $result = $conn->query("SELECT * FROM pelanggan");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['PelangganID'] . "'>" . $row['NamaPelanggan'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jumlah_bayar">Jumlah Bayar (Rp):</label>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" min="0" required>
                        </div>
                        <button type="submit" name="proses_transaksi" class="btn-success" <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>>Proses Transaksi</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>