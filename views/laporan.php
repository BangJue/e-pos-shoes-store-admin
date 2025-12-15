<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(($_SESSION['login'] ?? false) != true){
    echo "<script>alert('Login dulu!'); window.location='../login.php';</script>";
    exit;
}

$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

/* ================= STATISTIK CARD ================= */

// Transaksi Hari Ini
$qHariIni = $conn->query("SELECT COUNT(DISTINCT t.id_transaksi) AS total FROM transaksi t WHERE DATE(t.tanggal) = CURDATE()");
$totalHariIni = $qHariIni->fetch_assoc()['total'] ?? 0;

// Total Pendapatan (Filter)
$qPendapatan = $conn->query("SELECT COALESCE(SUM(d.subtotal),0) AS total FROM detail_transaksi d INNER JOIN transaksi t ON d.id_transaksi = t.id_transaksi WHERE DATE(t.tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir'");
$totalPendapatan = $qPendapatan->fetch_assoc()['total'] ?? 0;

// Produk Terlaris
$qFavorit = $conn->query("SELECT p.nama_produk, SUM(d.qty) AS jumlah FROM detail_transaksi d INNER JOIN produk p ON d.id_produk = p.id_produk INNER JOIN transaksi t ON d.id_transaksi = t.id_transaksi WHERE DATE(t.tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY p.id_produk ORDER BY jumlah DESC LIMIT 1");
$fav = $qFavorit->fetch_assoc();
$namaFavorit = $fav['nama_produk'] ?? '-';
$jumlahFavorit = $fav['jumlah'] ?? 0;

/* ================= DATA CHART (GRAFIK) ================= */

// 1. Hari Ini (Per Jam)
$qChartToday = $conn->query("
    SELECT DATE_FORMAT(tanggal, '%H:00') as label, SUM(total_bayar) as total 
    FROM transaksi 
    WHERE DATE(tanggal) = CURDATE() 
    GROUP BY label 
    ORDER BY label ASC
");
$dataToday = []; while($r = $qChartToday->fetch_assoc()) $dataToday[] = $r;

// 2. 1 Minggu (Per Hari)
$qChartWeek = $conn->query("
    SELECT DATE_FORMAT(tanggal, '%d-%m') as label, SUM(total_bayar) as total 
    FROM transaksi 
    WHERE tanggal >= DATE(NOW()) - INTERVAL 7 DAY 
    GROUP BY label 
    ORDER BY MIN(tanggal) ASC
");
$dataWeek = []; while($r = $qChartWeek->fetch_assoc()) $dataWeek[] = $r;

// 3. 1 Bulan (Per Hari)
$qChartMonth = $conn->query("
    SELECT DATE_FORMAT(tanggal, '%d-%m') as label, SUM(total_bayar) as total 
    FROM transaksi 
    WHERE tanggal >= DATE(NOW()) - INTERVAL 1 MONTH 
    GROUP BY label 
    ORDER BY MIN(tanggal) ASC
");
$dataMonth = []; while($r = $qChartMonth->fetch_assoc()) $dataMonth[] = $r;

// 4. All Time (Per Bulan)
$qChartAll = $conn->query("
    SELECT DATE_FORMAT(tanggal, '%M %Y') as label, SUM(total_bayar) as total 
    FROM transaksi 
    GROUP BY label 
    ORDER BY MIN(tanggal) ASC
");
$dataAll = []; while($r = $qChartAll->fetch_assoc()) $dataAll[] = $r;


/* ================= DATA TABLE ================= */

$qPenjualan = $conn->query("
    SELECT t.id_transaksi, DATE(t.tanggal) AS tanggal, p.nama_produk, d.qty, d.subtotal
    FROM detail_transaksi d
    INNER JOIN transaksi t ON d.id_transaksi = t.id_transaksi
    INNER JOIN produk p ON d.id_produk = p.id_produk
    WHERE DATE(t.tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    ORDER BY t.tanggal DESC
");

$qStokMenipis = $conn->query("SELECT id_produk,nama_produk,kategori,stok FROM produk WHERE stok < 10 ORDER BY stok ASC");
$qStokAll = $conn->query("SELECT id_produk,nama_produk,kategori,stok FROM produk ORDER BY nama_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan | EPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Hide Scrollbar */
        ::-webkit-scrollbar { width: 0; height: 0; }
        body {
            -ms-overflow-style: none;
            scrollbar-width: none;
            background: #f8fafc;
            scroll-behavior: smooth;
        }   

        /* Scroll Animation */
        .scroll-animate {
            opacity: 0;
            transform: translateY(50px) scale(0.97);
            transition: all 0.8s ease;
        }
        .scroll-animate.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        /* Button Chart Active State */
        .chart-btn.active {
            background-color: #2563eb; /* blue-600 */
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Pagination Style */
        .pagination-btn {
            padding: 0.25rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            background-color: white;
            color: #475569;
            transition: all 0.2s;
        }
        .pagination-btn:hover:not(:disabled) {
            background-color: #f1f5f9;
        }
        .pagination-btn.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>

    <script>
    function printTabel(id){
        // Clone element agar tidak merusak tampilan asli
        let originalTable = document.getElementById(id);
        
        // Buat string HTML, tapi hapus style "display: none" agar semua baris tercetak
        let htmlContent = originalTable.outerHTML.replace(/style="display: none;"/g, '');
        
        const w = window.open('');
        w.document.write('<html><head><script src="https://cdn.tailwindcss.com"><\/script>');
        w.document.write('<style>table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 8px; }</style>');
        w.document.write('</head><body class="p-6">');
        w.document.write('<h2 class="text-xl font-bold mb-4">Laporan Cetak</h2>');
        w.document.write(htmlContent);
        w.document.write('</body></html>');
        w.document.close();
        
        // Tunggu sebentar agar Tailwind load sebelum print (opsional)
        setTimeout(() => {
            w.print();
            w.close();
        }, 500);
    }
        
    function exportExcel(id,filename){
        let originalTable = document.getElementById(id);
        // Hapus style display:none agar semua row masuk excel
        let htmlContent = originalTable.outerHTML.replace(/style="display: none;"/g, '');
        
        let uri = 'data:application/vnd.ms-excel,' + encodeURIComponent(htmlContent);
        let link = document.createElement('a');
        link.href = uri;
        link.download = filename;
        link.click();
    }
    </script>
</head>

<body class="bg-gray-50">

<nav class="bg-blue-700 text-white shadow-lg px-6 py-4 flex justify-between items-center scroll-animate">
    <div class="flex items-center gap-3">
        <i class="fas fa-store text-3xl"></i>
        <h1 class="text-2xl font-bold">EPOS Admin</h1>
    </div>

    <ul class="flex items-center space-x-10 font-semibold">
        <li>
            <a href="index.php" class="flex items-center gap-2 hover:text-gray-200">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="relative">
            <button type="button" onclick="toggleDropdown(event)" class="flex items-center gap-2 hover:text-gray-200">
                <i class="fas fa-receipt"></i> <span>Transaksi</span> <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div id="dropdownMenu" class="hidden absolute left-0 top-full mt-2 bg-white text-black shadow-xl rounded-lg w-56 py-2 z-[9999]">
                <a href="../views/laporan.php" class="block px-4 py-3 flex gap-2 hover:bg-gray-100">
                    <i class="fas fa-chart-line"></i> <span>Laporan Penjualan</span>
                </a>
                <a href="../views/transaksi.php" class="block px-4 py-3 flex gap-2 hover:bg-gray-100">
                    <i class="fas fa-cash-register"></i> <span>Buat Transaksi</span>
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

<div class="p-10">

    <h2 class="text-3xl font-bold mb-6 text-blue-800 flex items-center space-x-3 scroll-animate">
        <i class="fas fa-chart-line"></i>
        <span>Laporan & Statistik</span>
    </h2>

    <form method="GET" class="bg-white p-5 rounded-xl shadow flex flex-wrap gap-4 items-end scroll-animate">
        <div>
            <label class="font-semibold">Dari Tanggal</label>
            <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="border px-3 py-2 rounded-lg">
        </div>
        <div>
            <label class="font-semibold">Sampai Tanggal</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="border px-3 py-2 rounded-lg">
        </div>
        <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:scale-105 transition">
            <i class="fa fa-filter"></i> Filter
        </button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 scroll-animate">
        <div class="bg-blue-600 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between items-center">
                <p>Total Transaksi Hari Ini</p>
                <i class="fas fa-shopping-cart text-3xl opacity-80"></i>
            </div>
            <h1 class="text-3xl font-bold mt-2"><?= $totalHariIni ?></h1>
        </div>
        <div class="bg-blue-700 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between items-center">
                <p>Total Pendapatan (Filter)</p>
                <i class="fas fa-money-bill-wave text-3xl opacity-80"></i>
            </div>
            <h1 class="text-3xl font-bold mt-2">Rp <?= number_format($totalPendapatan,0,',','.') ?></h1>
        </div>
        <div class="bg-blue-500 text-white p-6 rounded-xl shadow-xl hover:scale-105 transition">
            <div class="flex justify-between items-center">
                <p>Produk Terlaris</p>
                <i class="fas fa-star text-3xl opacity-80"></i>
            </div>
            <h1 class="text-xl font-bold mt-2"><?= $namaFavorit ?> (<?= $jumlahFavorit ?>x)</h1>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg mt-8 border scroll-animate">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chart-area text-blue-600"></i> Grafik Pendapatan
            </h3>
            
            <div class="flex bg-gray-100 p-1 rounded-lg">
                <button onclick="updateChart('today')" class="chart-btn active px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 transition">Hari Ini</button>
                <button onclick="updateChart('week')" class="chart-btn px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 transition">1 Minggu</button>
                <button onclick="updateChart('month')" class="chart-btn px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 transition">1 Bulan</button>
                <button onclick="updateChart('all')" class="chart-btn px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 transition">All Time</button>
            </div>
        </div>
        <div class="h-80 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="mt-10 flex justify-between items-center scroll-animate">
        <h2 class="text-xl font-bold text-blue-700">
            <i class="fa fa-table"></i> Laporan Penjualan
        </h2>
        <div class="flex gap-2">
            <button onclick="printTabel('tblPenjualan')" class="bg-black text-white px-4 py-2 rounded"><i class="fa fa-print"></i> Print</button>
            <button onclick="exportExcel('tblPenjualan','penjualan.xls')" class="bg-green-600 text-white px-4 py-2 rounded"><i class="fa fa-file-excel"></i> Excel</button>
        </div>
    </div>

    <div class="scroll-animate">
        <div id="tblPenjualanContainer" class="bg-white shadow mt-3 rounded-xl overflow-hidden">
            <table id="tblPenjualan" class="w-full table-fixed border-collapse">
                <thead class="bg-blue-700 text-white">
                    <tr>
                        <th class="p-3 w-16 text-center">ID</th>
                        <th class="p-3 w-40 text-center">Tanggal</th>
                        <th class="p-3 text-center">Produk</th>
                        <th class="p-3 w-20 text-center">Qty</th>
                        <th class="p-3 w-40 text-right">Subtotal</th>
                        <th class="p-3 w-24 text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($p = $qPenjualan->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-center"><?= $p['id_transaksi'] ?></td>
                        <td class="p-3 text-center"><?= $p['tanggal'] ?></td>
                        <td class="p-3 text-center"><?= $p['nama_produk'] ?></td>
                        <td class="p-3 text-center"><?= $p['qty'] ?></td>
                        <td class="p-3 text-right">Rp <?= number_format($p['subtotal'],0,',','.') ?></td>
                        <td class="p-3 text-center">
                            <button onclick="previewInvoice(<?= $p['id_transaksi'] ?>)" class="bg-gray-200 text-blue-600 hover:bg-blue-600 hover:text-white p-2 rounded transition-colors" title="Preview & Cetak">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="pagination-tblPenjualan" class="flex justify-end mt-4 gap-1"></div>
    </div>

    <div class="mt-14 flex justify-between items-center scroll-animate">
        <h2 class="text-xl font-bold text-red-600">
            <i class="fa fa-exclamation-triangle"></i> Stok Menipis
        </h2>
        <div class="flex gap-2">
            <button onclick="printTabel('tblMenipis')" class="bg-black text-white px-4 py-2 rounded"><i class="fa fa-print"></i> Print</button>
            <button onclick="exportExcel('tblMenipis','stok-menipis.xls')" class="bg-green-600 text-white px-4 py-2 rounded"><i class="fa fa-file-excel"></i> Excel</button>
        </div>
    </div>

    <div class="scroll-animate">
        <div id="tblMenipisContainer" class="bg-white shadow mt-3 rounded-xl overflow-hidden">
            <table id="tblMenipis" class="w-full table-fixed border-collapse">
                <thead class="bg-red-600 text-white">
                    <tr>
                        <th class="p-3 w-16 text-center">ID</th>
                        <th class="p-3 text-center">Nama Produk</th>
                        <th class="p-3 w-40 text-center">Kategori</th>
                        <th class="p-3 w-20 text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($s = $qStokMenipis->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-center"><?= $s['id_produk'] ?></td>
                        <td class="p-3 text-center"><?= $s['nama_produk'] ?></td>
                        <td class="p-3 text-center"><?= $s['kategori'] ?></td>
                        <td class="p-3 text-center text-red-600 font-bold"><?= $s['stok'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="pagination-tblMenipis" class="flex justify-end mt-4 gap-1"></div>
    </div>

    <div class="mt-14 flex justify-between items-center scroll-animate">
        <h2 class="text-xl font-bold text-gray-700">
            <i class="fa fa-box"></i> Stok Keseluruhan
        </h2>
        <div class="flex gap-2">
            <button onclick="printTabel('tblAll')" class="bg-black text-white px-4 py-2 rounded"><i class="fa fa-print"></i> Print</button>
            <button onclick="exportExcel('tblAll','stok-semua.xls')" class="bg-green-600 text-white px-4 py-2 rounded"><i class="fa fa-file-excel"></i> Excel</button>
        </div>
    </div>

    <div class="scroll-animate">
        <div id="tblAllContainer" class="bg-white shadow mt-3 rounded-xl overflow-hidden">
            <table id="tblAll" class="w-full table-fixed border-collapse">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="p-3 w-16 text-center">ID</th>
                        <th class="p-3 text-center">Nama Produk</th>
                        <th class="p-3 w-40 text-center">Kategori</th>
                        <th class="p-3 w-20 text-center">Stok</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($a = $qStokAll->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-center"><?= $a['id_produk'] ?></td>
                        <td class="p-3 text-center"><?= $a['nama_produk'] ?></td>
                        <td class="p-3 text-center"><?= $a['kategori'] ?></td>
                        <td class="p-3 text-center font-bold"><?= $a['stok'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="pagination-tblAll" class="flex justify-end mt-4 gap-1"></div>
    </div>

</div>

<div id="invoiceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-lg h-[85vh] flex flex-col overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
            <h3 class="font-bold text-gray-700"><i class="fas fa-receipt"></i> Preview Invoice</h3>
            <button onclick="closePreview()" class="text-gray-500 hover:text-red-500 text-2xl">&times;</button>
        </div>
        <div class="flex-1 bg-gray-200 relative">
            <iframe id="invoiceFrame" src="" class="w-full h-full border-0"></iframe>
        </div>
        <div class="bg-white px-4 py-3 border-t flex justify-end gap-3">
            <button onclick="closePreview()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Tutup</button>
            <button onclick="printFrame()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-2"><i class="fas fa-print"></i> Cetak Sekarang</button>
        </div>
    </div>
</div>

<script>
/* --- LOGIKA DROPDOWN --- */
function toggleDropdown(e) { e.stopPropagation(); document.getElementById('dropdownMenu').classList.toggle('hidden'); }
document.addEventListener('click', function () { const menu = document.getElementById('dropdownMenu'); if (!menu.classList.contains('hidden')) menu.classList.add('hidden'); });

/* --- LOGIKA ANIMASI SCROLL --- */
const observer = new IntersectionObserver(entries => { entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('show'); }); }, {threshold: 0.1});
document.querySelectorAll('.scroll-animate').forEach(el => observer.observe(el));

/* --- LOGIKA MODAL INVOICE --- */
function previewInvoice(id) { document.getElementById('invoiceModal').classList.remove('hidden'); document.getElementById('invoiceFrame').src = '../actions/invoice.php?id=' + id; }
function closePreview() { document.getElementById('invoiceModal').classList.add('hidden'); document.getElementById('invoiceFrame').src = ''; }
function printFrame() { document.getElementById('invoiceFrame').contentWindow.print(); }

/* --- LOGIKA CHART JS --- */
const datasets = {
    today: formatData(<?= json_encode($dataToday) ?>),
    week:  formatData(<?= json_encode($dataWeek) ?>),
    month: formatData(<?= json_encode($dataMonth) ?>),
    all:   formatData(<?= json_encode($dataAll) ?>)
};

function formatData(rawData) { return { labels: rawData.map(d => d.label), data: rawData.map(d => d.total) }; }

const ctx = document.getElementById('revenueChart').getContext('2d');
let revenueChart = new Chart(ctx, {
    type: 'line',
    data: { labels: datasets.today.labels, datasets: [{ label: 'Pendapatan (Rp)', data: datasets.today.data, borderColor: '#2563eb', backgroundColor: 'rgba(37, 99, 235, 0.1)', borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#ffffff', pointBorderColor: '#2563eb', pointRadius: 4, pointHoverRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', padding: 12, callbacks: { label: function(context) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw); } } } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#e2e8f0' }, ticks: { callback: function(value) { return 'Rp ' + (value / 1000) + 'k'; } } }, x: { grid: { display: false } } } }
});

function updateChart(period) {
    document.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active')); event.target.classList.add('active');
    revenueChart.data.labels = datasets[period].labels; revenueChart.data.datasets[0].data = datasets[period].data; revenueChart.update();
}
document.querySelector('.chart-btn').classList.add('active');


/* ========================================= */
/* LOGIKA PAGINATION OTOMATIS          */
/* ========================================= */

function paginateTable(tableId, rowsPerPage = 10) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const rowCount = rows.length;
    const pageCount = Math.ceil(rowCount / rowsPerPage);
    const paginationContainer = document.getElementById('pagination-' + tableId);

    // Jika data <= limit, tidak perlu pagination
    if (rowCount <= rowsPerPage) {
        if(paginationContainer) paginationContainer.innerHTML = '';
        return;
    }

    let currentPage = 1;

    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        renderButtons(page);
    }

    function renderButtons(activePage) {
        paginationContainer.innerHTML = '';

        // Tombol Previous (<)
        const prevBtn = document.createElement('button');
        prevBtn.innerText = '<';
        prevBtn.className = 'pagination-btn';
        prevBtn.disabled = activePage === 1;
        prevBtn.onclick = () => showPage(activePage - 1);
        paginationContainer.appendChild(prevBtn);

        // Angka Halaman
        for (let i = 1; i <= pageCount; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.className = `pagination-btn ${i === activePage ? 'active' : ''}`;
            btn.onclick = () => showPage(i);
            paginationContainer.appendChild(btn);
        }

        // Tombol Next (>)
        const nextBtn = document.createElement('button');
        nextBtn.innerText = '>';
        nextBtn.className = 'pagination-btn';
        nextBtn.disabled = activePage === pageCount;
        nextBtn.onclick = () => showPage(activePage + 1);
        paginationContainer.appendChild(nextBtn);
    }

    // Initialize Page 1
    showPage(1);
}

// Jalankan Pagination untuk semua tabel
document.addEventListener('DOMContentLoaded', () => {
    paginateTable('tblPenjualan', 10);
    paginateTable('tblMenipis', 10);
    paginateTable('tblAll', 10);
});
</script>

</body>
</html>