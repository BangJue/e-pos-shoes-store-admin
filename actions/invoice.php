<?php
session_start();
// Pastikan path koneksi benar
require_once __DIR__ . '/../config/koneksi.php';

// Cek Login (Opsional, matikan jika ingin bisa diakses publik)
if(($_SESSION['login'] ?? false) != true){
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Validasi ID Transaksi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID Transaksi tidak ditemukan.");
}

$id_transaksi = $_GET['id'];

// 1. AMBIL DATA HEADER TRANSAKSI (Total, Tanggal, Kasir)
$queryHeader = $conn->query("
    SELECT * FROM transaksi 
    WHERE id_transaksi = '$id_transaksi'
");

if ($queryHeader->num_rows == 0) {
    die("Data transaksi dengan ID #$id_transaksi tidak ditemukan di database.");
}

$header = $queryHeader->fetch_assoc();

// 2. AMBIL DATA DETAIL BARANG (Join dengan tabel produk untuk ambil nama)
$queryDetail = $conn->query("
    SELECT 
        d.id_detail,
        d.qty,
        d.subtotal,
        p.nama_produk
    FROM detail_transaksi d
    INNER JOIN produk p ON d.id_produk = p.id_produk
    WHERE d.id_transaksi = '$id_transaksi'
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?= $id_transaksi ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* CSS Khusus agar tampilan mirip struk belanja */
        body {
            font-family: 'Courier New', Courier, monospace; /* Font kasir */
            background-color: #fff;
            color: #000;
        }
        
        /* Saat diprint, hilangkan margin body agar pas di kertas */
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }

        .garis-putus {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body class="p-4">

    <div class="max-w-[400px] mx-auto bg-white">

        <div class="text-center mb-4">
            <h2 class="text-xl font-bold uppercase tracking-widest">EPOS SEPATU</h2>
            <p class="text-xs mt-1">Jl. Sukakarya</p>
            <p class="text-xs">Telp: 0895-6326-51921</p>
        </div>

        <div class="garis-putus"></div>
        <div class="flex justify-between text-xs mb-1">
            <span>No. Resi</span>
            <span>#<?= $id_transaksi ?></span>
        </div>
        <div class="flex justify-between text-xs mb-1">
            <span>Tanggal</span>
            <span><?= date('d/m/Y H:i', strtotime($header['tanggal'])) ?></span>
        </div>
        <div class="flex justify-between text-xs mb-1">
            <span>Kasir</span>
            <span class="uppercase"><?= htmlspecialchars($header['kasir'] ?? 'Admin') ?></span>
        </div>
        <div class="garis-putus"></div>

        <div class="mb-4">
            <table class="w-full text-xs">
                <?php 
                $cekTotal = 0;
                while($item = $queryDetail->fetch_assoc()): 
                    // Hitung harga satuan (Subtotal dibagi Qty)
                    $hargaSatuan = $item['subtotal'] / $item['qty'];
                    $cekTotal += $item['subtotal'];
                ?>
                <tr>
                    <td colspan="3" class="pt-2 font-bold">
                        <?= htmlspecialchars($item['nama_produk']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="pb-1 text-gray-600 pl-2">
                        <?= $item['qty'] ?> x <?= number_format($hargaSatuan, 0, ',', '.') ?>
                    </td>
                    <td class="pb-1 text-right font-semibold">
                        <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="garis-putus"></div>
        <div class="flex justify-between text-sm font-bold mt-2">
            <span>TOTAL TAGIHAN</span>
            <span>Rp <?= number_format($header['total_bayar'], 0, ',', '.') ?></span>
        </div>

        <div class="mt-8 text-center text-[10px] text-gray-500">
            <p>*** TERIMA KASIH ***</p>
            <p class="mt-1">Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan.</p>
        </div>

    </div>

</body>
</html>