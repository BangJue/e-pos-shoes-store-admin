<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    echo "<script>
        alert('Silahkan login terlebih dahulu!');
        window.location.href = '../login.php';
    </script>";
    exit;
}

// =========================
// STATISTIK DASHBOARD
// =========================
$qProduk     = $conn->query("SELECT COUNT(*) AS total_produk FROM produk");
$totalProduk = $qProduk->fetch_assoc()['total_produk'];

$qTransaksi     = $conn->query("SELECT COUNT(*) AS total_transaksi FROM transaksi");
$totalTransaksi = $qTransaksi->fetch_assoc()['total_transaksi'];

$qPendapatan = $conn->query("SELECT COALESCE(SUM(total_bayar),0) AS pendapatan FROM transaksi");
$pendapatan  = $qPendapatan->fetch_assoc()['pendapatan'];

$qTotalStok = $conn->query("SELECT COALESCE(SUM(stok),0) AS total_stok FROM produk");
$totalStok  = $qTotalStok->fetch_assoc()['total_stok'];

$stokStatus = ($totalStok < 20) ? "Rendah" : "Cukup";
$stokColor  = ($totalStok < 20) ? "bg-red-600" : "bg-green-600";

// =========================
// DATA GRAFIK
// =========================
$qChart = $conn->query("
    SELECT tanggal, SUM(total_bayar) AS total 
    FROM transaksi 
    GROUP BY tanggal 
    ORDER BY tanggal ASC 
    LIMIT 7
");
$labels = [];
$values = [];
while ($row = $qChart->fetch_assoc()) {
    $labels[] = $row['tanggal'];
    $values[] = $row['total'];
}

// =========================
// PRODUK TERLARIS
// =========================
$qPopular = $conn->query("
    SELECT p.nama_produk, p.gambar, SUM(d.qty) AS total_terjual
    FROM detail_transaksi d
    JOIN produk p ON d.id_produk = p.id_produk
    JOIN transaksi t ON d.id_transaksi = t.id_transaksi
    WHERE t.tanggal >= CURDATE() - INTERVAL 7 DAY
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body::-webkit-scrollbar {display:none;}
        body {background:#f8fafc;scroll-behavior:smooth;}

        /* Scroll Animation */
        .scroll-animate {
            opacity:0;
            transform:translateY(50px) scale(.97);
            transition:all .8s ease;
        }
        .scroll-animate.show {
            opacity:1;
            transform:translateY(0) scale(1);
        }

        .popular-card:hover {
            box-shadow:0 25px 50px rgba(30,64,175,.25);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="bg-blue-700 text-white shadow-lg px-6 py-4 flex justify-between items-center scroll-animate">
    <div class="flex items-center gap-3">
        <i class="fas fa-store text-3xl"></i>
        <h1 class="text-2xl font-bold">EPOS Admin</h1>
    </div>

    <ul class="flex items-center space-x-10 font-semibold">

        <li>
            <a href="index.php" class="flex items-center gap-2 hover:text-gray-200">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="relative">
    <button type="button" onclick="toggleDropdown(event)" class="flex items-center gap-2 hover:text-gray-200">
        <i class="fas fa-receipt"></i>
        <span>Transaksi</span>
        <i class="fas fa-chevron-down text-xs"></i>
    </button>

    <div id="dropdownMenu"
         class="hidden absolute left-0 top-full mt-2 bg-white text-black shadow-xl rounded-lg w-56 py-2 z-[9999]">
         
        <a href="../views/laporan.php" 
           class="block px-4 py-3 flex gap-2 hover:bg-gray-100">
            <i class="fas fa-chart-line"></i> 
            <span>Laporan Penjualan</span>
        </a>

        <a href="../views/transaksi.php" 
           class="block px-4 py-3 flex gap-2 hover:bg-gray-100">
            <i class="fas fa-cash-register"></i> 
            <span>Buat Transaksi</span>
        </a>

    </div>
</li>


        <li>
            <a href="produk.php" class="flex items-center gap-2 hover:text-gray-200">
                <i class="fas fa-box"></i> Produk
            </a>
        </li>

        <li>
            <a href="../actions/logout.php" class="flex items-center gap-2 hover:text-gray-200">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>

    </ul>
</nav>

<!-- CONTENT -->
<div class="p-8">

    <!-- HEADER -->
    <h2 class="text-3xl font-bold mb-6 text-blue-800 flex items-center gap-3 scroll-animate">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard Penjualan</span>
    </h2>

    <!-- STAT CARD -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 scroll-animate">
        <div class="bg-blue-600 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between">
                <h3>Total Produk</h3>
                <i class="fas fa-boxes text-3xl"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $totalProduk ?></p>
        </div>

        <div class="bg-blue-500 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between">
                <h3>Total Transaksi</h3>
                <i class="fas fa-shopping-cart text-3xl"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $totalTransaksi ?></p>
        </div>

        <div class="bg-blue-700 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between">
                <h3>Pendapatan</h3>
                <i class="fas fa-money-bill-wave text-3xl"></i>
            </div>
            <p class="text-3xl font-bold mt-2">Rp <?= number_format($pendapatan,0,',','.') ?></p>
        </div>

        <div class="<?= $stokColor ?> text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between">
                <h3>Total Stok</h3>
                <i class="fas fa-box-open text-3xl"></i>
            </div>
            <p class="text-4xl font-bold mt-2"><?= $totalStok ?></p>
            <p class="mt-2 font-semibold">Status: <?= $stokStatus ?></p>
        </div>
    </div>

    <!-- CHART -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-10 scroll-animate">
        <div class="bg-white shadow-xl p-6 rounded-xl border">
            <h3 class="text-xl font-bold mb-4 text-blue-800">
                <i class="fas fa-chart-bar mr-1"></i> Grafik Penjualan
            </h3>
            <canvas id="barChart"></canvas>
        </div>

        <div class="bg-white shadow-xl p-6 rounded-xl border">
            <h3 class="text-xl font-bold mb-4 text-blue-800">
                <i class="fas fa-chart-line mr-1"></i> Tren Pendapatan
            </h3>
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <!-- PRODUK TERLARIS -->
    <div class="flex flex-col items-center mt-20 scroll-animate">
        <h3 class="text-2xl font-bold text-blue-800 mb-8 flex items-center gap-3">
            <i class="fas fa-fire text-orange-500"></i>
            Produk Terlaris Minggu Ini
        </h3>

        <div class="flex flex-wrap justify-center gap-8">

            <?php if($qPopular->num_rows > 0): ?>
                <?php while($p = $qPopular->fetch_assoc()): ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-72 text-center popular-card transition hover:scale-105">

                    <div class="relative h-48 overflow-hidden">
                        <img src="../uploads/<?= $p['gambar'] ?>"
                             class="w-full h-full object-cover transition duration-500 hover:scale-110">
                        <div class="absolute top-3 left-3 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            <i class="fas fa-fire"></i> Terlaris
                        </div>
                    </div>

                    <div class="p-5">
                        <h4 class="text-lg font-bold text-gray-800">
                            <?= htmlspecialchars($p['nama_produk']) ?>
                        </h4>
                        <p class="text-gray-500 mt-1">
                            Terjual: <?= $p['total_terjual'] ?>
                        </p>
                    </div>

                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-gray-500 text-center py-10">
                    <i class="fas fa-box-open text-4xl mb-3"></i>
                    <p>Belum ada data produk terlaris</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- SCRIPT -->
<script>
function toggleDropdown(e) {
    e.stopPropagation(); 
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('hidden');
}

// klik luar = tutup dropdown
document.addEventListener('click', function () {
    const menu = document.getElementById('dropdownMenu');
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

// Scroll animation
const observer = new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
        if(entry.isIntersecting){
            entry.target.classList.add('show');
        }
    });
},{threshold:0.1});

document.querySelectorAll('.scroll-animate').forEach(el=>observer.observe(el));

// Chart
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(document.getElementById('barChart'),{
    type:'bar',
    data:{labels,datasets:[{label:'Total Penjualan',data:values,backgroundColor:'rgba(29,78,216,0.8)'}]}
});

new Chart(document.getElementById('lineChart'),{
    type:'line',
    data:{labels,datasets:[{label:'Pendapatan',data:values,borderColor:'rgba(37,99,235,1)',borderWidth:3,tension:.4}]}
});
</script>

</body>
</html>
