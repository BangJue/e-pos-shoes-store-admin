<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// 1. Pastikan request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/transaksi.php?error=invalid_method");
    exit();
}

// 2. Validasi Input Dasar (ID dan Qty wajib ada)
if (empty($_POST['id_produk']) || empty($_POST['qty'])) {
    header("Location: ../views/transaksi.php?error=data_kosong");
    exit();
}

$id_produk = intval($_POST['id_produk']);
$qty       = intval($_POST['qty']);
$tanggal   = date("Y-m-d H:i:s"); // Gunakan datetime agar lebih presisi (jam tercatat)

// 3. Ambil data produk dari Database (Harga & Stok Real)
//    PENTING: Jangan ambil harga dari $_POST untuk menghindari manipulasi user.
$stmt = $conn->prepare("SELECT harga, stok FROM produk WHERE id_produk = ?");
if (!$stmt) {
    header("Location: ../views/transaksi.php?error=db_error");
    exit();
}
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: ../views/transaksi.php?error=produk_tidak_ditemukan");
    exit();
}

$produk = $res->fetch_assoc();
$harga_asli = intval($produk['harga']);
$stok_asli  = intval($produk['stok']);

// 4. Cek Stok
if ($stok_asli < $qty) {
    header("Location: ../views/transaksi.php?error=stok_kurang");
    exit();
}

// 5. Hitung Total Secara Server-Side (Lebih Aman)
$total_bayar = $harga_asli * $qty;

// Ambil username kasir (jika login), jika tidak ada set NULL atau 'Admin'
$kasir = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; 

// =================================================
// MULAI TRANSAKSI DATABASE (ACID)
// =================================================
$conn->begin_transaction();

try {
    // A. Insert ke tabel `transaksi`
    // Kolom DB: id_transaksi (AI), tanggal, total_bayar, kasir
    $sqlTrans = "INSERT INTO transaksi (tanggal, total_bayar, kasir) VALUES (?, ?, ?)";
    $stmtTrans = $conn->prepare($sqlTrans);
    if (!$stmtTrans) throw new Exception("Gagal prepare transaksi: " . $conn->error);
    
    $stmtTrans->bind_param("sis", $tanggal, $total_bayar, $kasir);
    
    if (!$stmtTrans->execute()) {
        throw new Exception("Gagal simpan transaksi: " . $stmtTrans->error);
    }
    
    // Ambil ID Transaksi yang baru saja dibuat
    $id_transaksi_baru = $conn->insert_id;

    // B. Insert ke tabel `detail_transaksi`
    // Kolom DB: id_detail (AI), id_transaksi, id_produk, qty, subtotal
    // Karena ini transaksi tunggal, subtotal = total_bayar
    $sqlDetail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES (?, ?, ?, ?)";
    $stmtDetail = $conn->prepare($sqlDetail);
    if (!$stmtDetail) throw new Exception("Gagal prepare detail: " . $conn->error);

    $stmtDetail->bind_param("iiii", $id_transaksi_baru, $id_produk, $qty, $total_bayar);
    
    if (!$stmtDetail->execute()) {
        throw new Exception("Gagal simpan detail transaksi: " . $stmtDetail->error);
    }

    // C. Kurangi Stok Produk
    $sqlUpdate = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if (!$stmtUpdate) throw new Exception("Gagal prepare update stok: " . $conn->error);

    $stmtUpdate->bind_param("ii", $qty, $id_produk);
    
    if (!$stmtUpdate->execute()) {
        throw new Exception("Gagal update stok: " . $stmtUpdate->error);
    }

    // Jika semua lancar, COMMIT perubahan
    $conn->commit();

    // Redirect Sukses
    header("Location: ../views/transaksi.php?sukses=1");
    exit();

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan (ROLLBACK)
    $conn->rollback();
    
    // Opsional: Log error asli untuk developer
    error_log("Transaction Error: " . $e->getMessage());

    // Redirect Gagal
    header("Location: ../views/transaksi.php?error=transaksi_gagal");
    exit();
}