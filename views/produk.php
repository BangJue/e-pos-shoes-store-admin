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

// Ambil data produk
$qProduk = $conn->query("SELECT * FROM produk ORDER BY id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk | EPOS</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

    <style>
        body::-webkit-scrollbar {display:none;}
        body {background:#f8fafc; scroll-behavior:smooth;}

        /* Animasi Scroll */
        .scroll-animate {
            opacity: 0;
            transform: translateY(50px) scale(0.97);
            transition: all 0.8s ease;
        }
        .scroll-animate.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .overlay { background: rgba(0,0,0,0.55); }
        
        .outline-strong:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }
    </style>
</head>

<body class="bg-white text-black">

<nav class="bg-blue-700 text-white shadow-lg px-6 py-4 flex justify-between items-center scroll-animate relative z-50">
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

<div class="p-8">

    <h2 class="text-3xl font-bold mb-6 text-blue-800 flex items-center space-x-3 scroll-animate">
        <i class="fas fa-box"></i>
        <span>Daftar Produk</span>
    </h2>

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6 scroll-animate">
        <div class="relative w-full md:w-1/3">
            <i class="fa fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" id="searchInput" onkeyup="searchTable()" 
                placeholder="Cari nama produk atau kategori..."
                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg outline-strong transition shadow-sm">
        </div>

        <button onclick="openPopup()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition transform hover:scale-105">
            <i class="fa fa-plus"></i> Tambah Produk
        </button>
    </div>

    <div class="overflow-x-auto shadow-xl rounded-xl border border-gray-200 bg-white scroll-animate">
        
        <?php if($qProduk->num_rows > 0): ?>
        <table class="w-full text-left" id="produkTable">
            <thead class="bg-blue-700 text-white">
                <tr>
                    <th class="p-4">No</th>
                    <th class="p-4">Gambar</th>
                    <th class="p-4">Nama Produk</th>
                    <th class="p-4">Kategori</th>
                    <th class="p-4">Harga</th>
                    <th class="p-4">Stok</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $no=1; while ($row = $qProduk->fetch_assoc()) { 
                    // REVISI 2: Logika Warna Row berdasarkan Stok + Hover Effect
                    $rowClass = ""; 
                    if($row['stok'] == 0) {
                        // Stok Habis: Merah muda, hover merah sedikit lebih tua
                        $rowClass = "bg-red-50 hover:bg-red-100"; 
                    } elseif($row['stok'] < 10) {
                        // Stok Menipis: Kuning muda, hover kuning sedikit lebih tua
                        $rowClass = "bg-yellow-50 hover:bg-yellow-100";
                    } else {
                        // Stok Aman: Putih, hover abu-abu
                        $rowClass = "bg-white hover:bg-gray-100";
                    }
                ?>
                <tr class="<?= $rowClass ?> transition border-b text-gray-700">
                    <td class="p-4"><?= $no ?></td>
                    
                    <td class="p-4">
                        <div class="w-24 h-24 rounded-lg overflow-hidden border shadow-sm group relative bg-white">
                            <img src="../uploads/<?= $row['gambar'] ?>" class="w-full h-full object-cover">
                            <a href="../uploads/<?= $row['gambar'] ?>" target="_blank" 
                               class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer text-white">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </td>

                    <td class="p-4 font-semibold text-lg"><?= $row['nama_produk'] ?></td>
                    
                    <td class="p-4"><?= $row['kategori'] ?></td>
                    
                    <td class="p-4 text-blue-700 font-semibold">
                        Rp <?= number_format($row['harga'],0,',','.') ?>
                    </td>
                    
                    <td class="p-4">
                        <?= $row['stok'] ?> Unit
                    </td>
                    
                    <td class="p-4 flex justify-center space-x-6 text-lg">
                        <button onclick="editProduk(
                            '<?= $row['id_produk'] ?>',
                            '<?= $row['nama_produk'] ?>',
                            '<?= $row['kategori'] ?>',
                            '<?= $row['harga'] ?>',
                            '<?= $row['stok'] ?>'
                        )" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <a href="../actions/delete_product.php?id=<?= $row['id_produk'] ?>"
                           onclick="return confirm('Yakin ingin menghapus produk ini?')"
                           class="text-red-600 hover:text-red-800 transition" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php $no++; } ?>
            </tbody>
        </table>

        <div id="noDataMessage" class="hidden p-8 text-center text-gray-500">
            <i class="far fa-sad-tear text-4xl mb-2"></i>
            <p>Produk tidak ditemukan.</p>
        </div>

        <?php else: ?>
            <div class="p-10 text-center text-gray-400 flex flex-col items-center">
                <i class="fas fa-box-open text-6xl mb-4 text-gray-300"></i>
                <h3 class="text-xl font-bold text-gray-600">Belum ada produk</h3>
                <p class="mb-4">Silahkan tambahkan produk pertama Anda.</p>
                <button onclick="openPopup()" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition shadow">
                    Tambah Sekarang
                </button>
            </div>
        <?php endif; ?>

    </div>
</div>


<div id="popup" class="fixed inset-0 hidden justify-center items-center overlay z-[100]">
    <div class="bg-white text-black p-8 w-96 rounded-2xl shadow-2xl fade-in transform scale-100 transition-all">
        <h3 id="popup-title" class="text-2xl font-bold mb-6 text-center text-blue-800 flex items-center justify-center gap-2">
            <i class="fas fa-box-open"></i> <span>Tambah Produk</span>
        </h3>

        <form id="form-produk" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="id_produk" name="id_produk">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Produk</label>
                <input class="w-full border border-gray-300 p-2 rounded-lg outline-strong" id="nama_produk" name="nama_produk" placeholder="Contoh: Kopi Susu" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                <input class="w-full border border-gray-300 p-2 rounded-lg outline-strong" id="kategori" name="kategori" placeholder="Contoh: Minuman" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" class="w-full border border-gray-300 p-2 rounded-lg outline-strong" id="harga" name="harga" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Stok</label>
                    <input type="number" class="w-full border border-gray-300 p-2 rounded-lg outline-strong" id="stok" name="stok" placeholder="0" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Gambar Produk</label>
                <input type="file" class="w-full border border-gray-300 p-2 rounded-lg bg-gray-50 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="gambar" name="gambar">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closePopup()"
                    class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg font-bold transition">
                    Batal
                </button>
                <button type="submit"
                    class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold shadow-lg transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// --- ANIMASI SCROLL ---
const observer = new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
        if(entry.isIntersecting){
            entry.target.classList.add('show');
        }
    });
},{threshold:0.1});
document.querySelectorAll('.scroll-animate').forEach(el=>observer.observe(el));

// --- DROPDOWN NAV ---
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

// --- FITUR SEARCH ---
function searchTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("produkTable");
    let tr = table.getElementsByTagName("tr");
    let hasData = false;

    for (let i = 1; i < tr.length; i++) {
        let tdNama = tr[i].getElementsByTagName("td")[2];
        let tdKat  = tr[i].getElementsByTagName("td")[3];

        if (tdNama || tdKat) {
            let txtValueNama = tdNama.textContent || tdNama.innerText;
            let txtValueKat  = tdKat.textContent || tdKat.innerText;

            if (txtValueNama.toLowerCase().indexOf(filter) > -1 || txtValueKat.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
                hasData = true;
            } else {
                tr[i].style.display = "none";
            }
        }
    }

    let msg = document.getElementById("noDataMessage");
    if(!hasData && filter !== "") {
        msg.classList.remove("hidden");
        table.classList.add("hidden");
    } else {
        msg.classList.add("hidden");
        table.classList.remove("hidden");
    }
}

// --- POPUP LOGIC ---
function openPopup() {
    document.getElementById("popup").style.display = "flex";
    document.getElementById("popup-title").innerHTML = "<i class='fas fa-plus-circle'></i> <span>Tambah Produk</span>";
    document.getElementById("form-produk").action = "../actions/add_product.php";
    document.getElementById("form-produk").reset();
    document.getElementById("id_produk").value = "";
    document.getElementById("gambar").required = true;
}

function editProduk(id, nama, kategori, harga, stok) {
    document.getElementById("popup").style.display = "flex";
    document.getElementById("popup-title").innerHTML = "<i class='fas fa-edit'></i> <span>Edit Produk</span>";
    document.getElementById("form-produk").action = "../actions/update_product.php";
    document.getElementById("id_produk").value = id;
    document.getElementById("nama_produk").value = nama;
    document.getElementById("kategori").value = kategori;
    document.getElementById("harga").value = harga;
    document.getElementById("stok").value = stok;
    document.getElementById("gambar").required = false;
}

function closePopup() {
    document.getElementById("popup").style.display = "none";
}
</script>

</body>
</html>