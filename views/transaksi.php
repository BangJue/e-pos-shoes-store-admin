<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if(($_SESSION['login'] != true)) {
    echo "
        <script>
            alert('Silahkan login terlebih dahulu!');
            window.location.href = '../login.php';
        </script>
    ";
    exit;
}

// Ambil semua produk
$qProduk = $conn->query("SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Transaksi</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

    <style>
        body::-webkit-scrollbar {display:none;}
        body {background:#f8fafc; scroll-behavior:smooth;}

        /* --- ANIMASI DARI INDEX.PHP --- */
        .scroll-animate {
            opacity: 0;
            transform: translateY(50px) scale(0.97);
            transition: all 0.8s ease;
        }
        .scroll-animate.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .produk-card:hover .overlay { opacity: 1; }

        /* Fit Image */
        .fit-img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            background-color: #f8fafc;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        /* Filter Stok Habis (Gelap) */
        .img-oos {
            filter: grayscale(100%) brightness(60%);
        }

        .outline-strong {
            border: 2px solid #cbd5f5 !important;
        }
        .outline-strong:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>

<body class="bg-white">

<nav class="bg-blue-700 text-white shadow-lg px-6 py-4 flex justify-between items-center scroll-animate relative z-50">
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

<div class="p-8">

    <h2 class="text-3xl font-bold mb-6 text-blue-800 flex items-center gap-3 scroll-animate">
        <i class="fas fa-cash-register"></i>
        <span>Buat Transaksi</span>
    </h2>

    <div class="flex justify-end flex-wrap gap-4 mb-6 scroll-animate">
        <div class="relative">
            <i class="fa fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" id="search" placeholder="Cari produk..."
                class="pl-10 pr-4 py-2 w-64 rounded-lg outline-strong transition">
        </div>

        <input type="number" id="minHarga" placeholder="Harga Min"
            class="px-4 py-2 w-40 rounded-lg outline-strong transition">

        <input type="number" id="maxHarga" placeholder="Harga Max"
            class="px-4 py-2 w-40 rounded-lg outline-strong transition">

        <button onclick="filterProduk()"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow">
            <i class="fa fa-filter"></i> Filter
        </button>
    </div>

    <div id="produkContainer" class="grid grid-cols-5 gap-6">

        <?php 
        $i = 0; // Counter untuk delay animasi
        while($p = $qProduk->fetch_assoc()): 
            $stokHabis = ($p['stok'] <= 0);
            $delay = $i * 150; // Delay bertingkat: 0ms, 100ms, 200ms, dst
            $i++;
        ?>
        
        <div 
            class="produk-item relative produk-card bg-white shadow-lg p-4 rounded-xl border hover:scale-[1.03] transition scroll-animate"
            style="transition-delay: <?= $delay ?>ms;"
            data-nama="<?= strtolower($p['nama_produk']) ?>"
            data-harga="<?= $p['harga'] ?>">

            <img src="../uploads/<?= $p['gambar'] ?>" class="fit-img <?= $stokHabis ? 'img-oos' : '' ?>">

            <div class="overlay absolute inset-0 <?= $stokHabis ? 'bg-black/20' : 'bg-black/50' ?> flex items-center justify-center
                        opacity-0 transition rounded-xl">
                
                <?php if(!$stokHabis): ?>
                    <button 
                        onclick="openForm(
                            <?= $p['id_produk'] ?>, 
                            '<?= $p['nama_produk'] ?>', 
                            <?= $p['harga'] ?>, 
                            <?= $p['stok'] ?>
                        )"
                        class="bg-blue-600 text-white px-4 py-2 rounded-full shadow hover:bg-blue-700 transform hover:scale-105 transition">
                        <i class="fa fa-shopping-cart"></i> Pesan
                    </button>
                <?php else: ?>
                    <button disabled
                        class="bg-red-600/90 text-white px-4 py-2 rounded-full shadow cursor-not-allowed opacity-90 font-semibold">
                        <i class="fa fa-times-circle"></i> Out of Stock
                    </button>
                <?php endif; ?>

            </div>

            <h3 class="text-lg font-semibold mt-3 <?= $stokHabis ? 'text-gray-500' : '' ?>">
                <?= $p['nama_produk'] ?>
            </h3>
            
            <p class="text-blue-700 font-bold">
                Rp <?= number_format($p['harga'],0,',','.') ?>
            </p>
            
            <p class="<?= $stokHabis ? 'text-red-600 font-bold' : 'text-gray-600' ?> text-sm">
                Stok: <?= $p['stok'] ?>
            </p>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="popup" class="hidden fixed inset-0 bg-black/60 z-[99999] flex justify-center items-center backdrop-blur-sm transition-opacity">
    <div class="bg-white p-6 rounded-2xl shadow-2xl w-96 transform transition-all scale-100">
        
        <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
            <i class="fas fa-cart-plus text-blue-600"></i>
            Tambah Pesanan
        </h3>

        <form action="../actions/transaksi.php" method="POST">
            <input type="hidden" name="id_produk" id="popup_id">
            <input type="hidden" id="popup_stok"> 

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Produk</label>
                <input type="text" id="popup_nama" readonly 
                       class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-gray-600">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Harga Satuan</label>
                <input type="text" id="popup_harga" readonly 
                       class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-gray-600">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Beli</label>
                <input type="number" name="qty" id="popup_qty" required min="1" oninput="hitungTotal()" 
                       class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                       placeholder="Masukkan jumlah...">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Total Bayar</label>
                <input type="text" id="popup_total" readonly 
                       class="w-full bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 font-bold text-blue-800 text-lg">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeForm()" 
                        class="px-5 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-800 font-semibold transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white font-bold shadow-lg transition">
                    <i class="fas fa-shopping-cart mr-1"></i> Beli
                </button>
            </div>
        </form>

    </div>
</div>

<script>
// --- DROPDOWN LOGIC ---
function toggleDropdown(e) {
    e.stopPropagation(); 
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('hidden');
}

document.addEventListener('click', function () {
    const menu = document.getElementById('dropdownMenu');
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

// --- ANIMATION OBSERVER (Diambil dari index.php) ---
const observer = new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
        if(entry.isIntersecting){
            entry.target.classList.add('show');
        }
    });
},{threshold:0.1});

// Terapkan observer ke semua elemen dengan class scroll-animate
document.querySelectorAll('.scroll-animate').forEach(el=>observer.observe(el));


// --- POPUP & TRANSACTION LOGIC ---
function openForm(id, nama, harga, stok) {
    document.getElementById('popup').classList.remove('hidden');
    
    document.getElementById('popup_id').value = id;
    document.getElementById('popup_nama').value = nama;
    document.getElementById('popup_harga').value = harga;
    document.getElementById('popup_stok').value = stok;
    
    document.getElementById('popup_qty').value = '';
    document.getElementById('popup_total').value = '';
    
    setTimeout(() => document.getElementById('popup_qty').focus(), 100);
}

function closeForm() {
    document.getElementById('popup').classList.add('hidden');
}

function hitungTotal() {
    let harga = parseInt(document.getElementById('popup_harga').value) || 0;
    let qty   = parseInt(document.getElementById('popup_qty').value) || 0;
    let stok  = parseInt(document.getElementById('popup_stok').value) || 0;

    // Validasi stok sederhana di client side
    if(qty > stok) {
        alert('Stok tidak mencukupi!');
        document.getElementById('popup_qty').value = stok;
        qty = stok;
    }

    if (!qty) {
        document.getElementById('popup_total').value = '';
        return;
    }

    document.getElementById('popup_total').value = harga * qty;
}

// --- FILTER LOGIC ---
document.getElementById('search').addEventListener('input', function(){
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.produk-item').forEach(item => {
        const nama = item.getAttribute('data-nama');
        // Reset animasi saat search agar user tau item berubah (opsional)
        if(nama.includes(keyword)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function filterProduk(){
    const min = parseInt(document.getElementById('minHarga').value) || 0;
    const max = parseInt(document.getElementById('maxHarga').value) || Infinity;

    document.querySelectorAll('.produk-item').forEach(item => {
        const harga = parseInt(item.getAttribute('data-harga'));
        item.style.display = (harga >= min && harga <= max) ? 'block' : 'none';
    });
}
</script>

</body>
</html>