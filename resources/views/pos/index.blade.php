<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kasir - Cabang {{ data_get(config('app.branch'), 'name', 'Aktif') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }
            #receipt-print-area, #receipt-print-area * {
                visibility: visible !important;
            }
            #receipt-print-area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 78mm !important;
                margin: 0 !important;
                padding: 10px !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 h-screen flex flex-col overflow-hidden">
    @if (auth()->user()->role === 'admin_cabang')
        <div class="flex h-screen flex-col overflow-hidden lg:flex-row">
            <aside id="admin-sidebar" class="flex w-full shrink-0 flex-col gap-4 bg-slate-900 p-4 text-white lg:h-screen lg:w-72 lg:gap-8 lg:p-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">Minimarket</p>
                    <h1 class="mt-2 text-2xl font-bold">Admin Cabang</h1>
                </div>

                <nav class="flex flex-wrap gap-2 lg:flex-col" aria-label="Navigasi admin cabang">
                    <a href="{{ route('admin.cabang.dashboard') }}"
                        class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.stok.index') }}"
                        class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
                        Manajemen Stok
                    </a>
                    <a href="{{ route('pos.index') }}" aria-current="page"
                        class="rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold text-white">
                        Kasir POS
                    </a>
                    <a href="{{ route('pos.riwayat') }}"
                        class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
                        Riwayat Transaksi
                    </a>
                </nav>

                <div class="mt-auto hidden rounded-xl border border-white/10 bg-white/5 p-4 lg:block">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Cabang aktif</p>
                    <p class="mt-2 text-lg font-semibold">{{ data_get(config('app.branch'), 'name', 'Cabang') }}</p>
                    <p class="mt-1 font-mono text-xs text-slate-400">
                        {{ data_get(config('app.branch'), 'id', '') }}
                    </p>
                </div>
            </aside>

            <main class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
    @endif

    <!-- Navbar -->
    <header class="bg-blue-600 text-white px-6 py-3 flex flex-wrap justify-between items-center gap-3 shadow-md">
        <div class="flex items-center gap-3">
            @if (auth()->user()->role === 'admin_cabang')
                <button type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="true"
                    aria-label="Tutup menu navigasi"
                    class="rounded-lg bg-blue-800 p-2 text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            @endif
            <h1 class="text-lg font-bold">Minimarket POS</h1>
            <span class="bg-blue-800 text-xs px-2 py-1 rounded">
                Cabang {{ data_get(config('app.branch'), 'name', 'Aktif') }}
            </span>
        </div>
        <div class="flex items-center gap-3">
            <!-- Tombol Riwayat Transaksi -->
            <a href="{{ route('pos.riwayat') }}"
                class="flex items-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Transaksi
            </a>

            <span class="text-sm font-medium">
                {{ auth()->user()->role === 'admin_cabang' ? 'Admin Cabang' : 'Kasir' }}:
                {{ auth()->user()->username }}
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded font-semibold transition">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Main Section -->
    <div class="flex min-h-0 flex-1 overflow-hidden">
        <!-- Panel Produk (Kiri) -->
        <div class="w-2/3 p-4 overflow-y-auto border-r border-slate-200">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-slate-800">Daftar Produk</h2>
                <span class="text-xs text-slate-500 font-medium">Klik produk untuk menambah ke keranjang</span>
            </div>
            <div class="grid grid-cols-3 gap-4">
                @foreach ($produk as $item)
                    <div onclick="addToCart('{{ $item->id_produk }}', '{{ $item->nama_produk }}', {{ $item->harga_jual }}, {{ $item->stok->jumlah_stok ?? 0 }})"
                        class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 cursor-pointer hover:border-blue-500 hover:shadow-md transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-1">
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">{{ $item->id_produk }}</span>
                                @if (!empty($item->kategori->nama_kategori))
                                    <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-medium">{{ $item->kategori->nama_kategori }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-slate-800 mt-2 text-sm leading-snug">{{ $item->nama_produk }}</h3>
                        </div>
                        <div class="mt-3">
                            <p class="text-blue-600 font-bold text-sm">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Stok: <span class="font-bold {{ ($item->stok->jumlah_stok ?? 0) <= 10 ? 'text-amber-600' : 'text-slate-700' }}">{{ $item->stok->jumlah_stok ?? 0 }}</span></p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Panel Keranjang & Checkout (Kanan) -->
        <div class="w-1/3 bg-white p-4 flex flex-col justify-between shadow-lg">
            <div>
                <div class="flex justify-between items-center mb-3 border-b pb-2">
                    <h2 class="text-lg font-semibold text-slate-800">Keranjang Belanja</h2>
                    <button onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-medium">Kosongkan</button>
                </div>
                <div class="overflow-y-auto max-h-[350px] space-y-2" id="cart-list">
                    <p class="text-center text-slate-400 text-sm py-8">Belum ada barang di keranjang</p>
                </div>
            </div>

            <!-- Bagian Ringkasan & Bayar -->
            <div class="border-t pt-4 space-y-3">
                <div class="flex justify-between items-center text-lg font-bold text-slate-800">
                    <span>Total</span>
                    <span id="total-price" class="text-blue-600">Rp 0</span>
                </div>

                <div class="space-y-1.5">
                    <label for="pay-amount" class="block text-xs text-slate-600 font-medium">Uang Pembayaran (Tunai)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">Rp</span>
                        <input type="text" inputmode="numeric" id="pay-amount" oninput="formatRupiahInput(this)" placeholder="0"
                            class="w-full pl-9 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-slate-800">
                    </div>
                </div>

                <div class="flex justify-between items-center text-sm font-semibold text-slate-600">
                    <span>Kembalian</span>
                    <span id="change-amount" class="text-slate-800 font-bold">Rp 0</span>
                </div>

                <button id="btn-checkout" onclick="processCheckout()"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition duration-200 shadow-md flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Selesaikan Transaksi
                </button>
            </div>
        </div>
    </div>

    @if (auth()->user()->role === 'admin_cabang')
            </main>
        </div>
    @endif

    <!-- Modal Nota / Struk Pembayaran -->
    <div id="modal-nota" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 transform transition-all">
            <!-- Header Modal (Non-print) -->
            <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex justify-between items-center print:hidden">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <h3 class="font-bold text-slate-800 text-sm">Transaksi Berhasil</h3>
                </div>
                <button onclick="closeNotaModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Area Struk Fisik / Thermal Print -->
            <div id="receipt-print-area" class="p-6 font-mono text-xs text-slate-800 bg-white">
                <div class="text-center pb-3 border-b border-dashed border-slate-300 space-y-1">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">MINIMARKET MULTI-CABANG</h2>
                    <p id="nota-cabang" class="font-semibold text-slate-600">Cabang {{ data_get(config('app.branch'), 'name', 'Malang') }}</p>
                    <p class="text-[10px] text-slate-400">Jl. Raya No. 123 • Telp: (0341) 555-789</p>
                </div>

                <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-slate-500">No Faktur:</span>
                        <span id="nota-faktur" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tanggal:</span>
                        <span id="nota-tanggal" class="text-slate-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kasir:</span>
                        <span id="nota-kasir" class="text-slate-700">-</span>
                    </div>
                </div>

                <!-- Daftar Item Belanja -->
                <div class="py-2.5 border-b border-dashed border-slate-300">
                    <div class="space-y-2" id="nota-items">
                        <!-- Dynamic items rendered by JS -->
                    </div>
                </div>

                <!-- Ringkasan Pembayaran -->
                <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 text-xs">
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>TOTAL</span>
                        <span id="nota-total">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tunai (Bayar)</span>
                        <span id="nota-bayar">Rp 0</span>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-800">
                        <span>Kembalian</span>
                        <span id="nota-kembalian">Rp 0</span>
                    </div>
                </div>

                <div class="text-center pt-3 text-[10px] text-slate-400 space-y-0.5">
                    <p class="font-semibold text-slate-700">*** TERIMA KASIH ***</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan</p>
                </div>
            </div>

            <!-- Tombol Aksi (Print: Hidden) -->
            <div class="bg-slate-50 border-t border-slate-200 px-5 py-3.5 flex gap-2 print:hidden">
                <button onclick="window.print()"
                    class="flex-1 flex items-center justify-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 rounded-xl text-xs transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Struk
                </button>
                <button onclick="closeNotaModal()"
                    class="flex-1 flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-xl text-xs transition shadow-sm">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Script Interaksi Kasir -->
    <script>
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                const sidebar = document.getElementById(sidebarToggle.getAttribute('aria-controls'));
                const isExpanded = sidebarToggle.getAttribute('aria-expanded') === 'true';
                sidebar.style.display = isExpanded ? 'none' : '';
                sidebarToggle.setAttribute('aria-expanded', String(!isExpanded));
                sidebarToggle.setAttribute('aria-label', isExpanded ? 'Buka menu navigasi' : 'Tutup menu navigasi');
            });
        }

        let cart = [];
        let total = 0;

        function addToCart(id, name, price, maxStock) {
            let item = cart.find(i => i.id_produk === id);
            if (item) {
                if (item.jumlah < maxStock) {
                    item.jumlah++;
                } else {
                    alert('Stok barang tidak mencukupi!');
                }
            } else {
                if (maxStock > 0) {
                    cart.push({ id_produk: id, nama_produk: name, harga_jual: price, jumlah: 1 });
                } else {
                    alert('Stok barang habis!');
                }
            }
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cart-list');
            list.innerHTML = '';
            total = 0;

            if (cart.length === 0) {
                list.innerHTML = '<p class="text-center text-slate-400 text-sm py-8">Belum ada barang di keranjang</p>';
                document.getElementById('total-price').innerText = 'Rp 0';
                calculateChange();
                return;
            }

            cart.forEach((item, index) => {
                total += item.harga_jual * item.jumlah;
                list.innerHTML += `
                    <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-lg border text-sm">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800">${item.nama_produk}</p>
                            <p class="text-xs text-slate-500">Rp ${item.harga_jual.toLocaleString('id-ID')} x ${item.jumlah}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQty(${index}, -1)" class="px-2 py-0.5 bg-slate-200 rounded font-bold text-xs hover:bg-slate-300">-</button>
                            <span class="text-xs font-bold">${item.jumlah}</span>
                            <button onclick="updateQty(${index}, 1)" class="px-2 py-0.5 bg-slate-200 rounded font-bold text-xs hover:bg-slate-300">+</button>
                        </div>
                    </div>
                `;
            });

            document.getElementById('total-price').innerText = 'Rp ' + total.toLocaleString('id-ID');
            calculateChange();
        }

        function clearCart() {
            if (cart.length > 0 && confirm('Kosongkan semua barang di keranjang?')) {
                cart = [];
                renderCart();
            }
        }

        function updateQty(index, change) {
            cart[index].jumlah += change;
            if (cart[index].jumlah <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function formatRupiahInput(input) {
            let raw = input.value.replace(/\D/g, '');
            if (raw === '') {
                input.value = '';
            } else {
                input.value = Number(raw).toLocaleString('id-ID');
            }
            calculateChange();
        }

        function getPayAmount() {
            const raw = document.getElementById('pay-amount').value.replace(/\D/g, '');
            return parseFloat(raw) || 0;
        }

        function calculateChange() {
            const pay = getPayAmount();
            const change = pay - total;
            document.getElementById('change-amount').innerText = 'Rp ' + (change > 0 ? change.toLocaleString('id-ID') : 0);
        }

        function processCheckout() {
            const pay = getPayAmount();
            if (cart.length === 0) return alert('Keranjang masih kosong!');
            if (pay < total) return alert('Nominal pembayaran masih kurang!');

            const btn = document.getElementById('btn-checkout');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span>Memproses Transaksi...</span>';

            fetch('/pos/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ cart: cart, total_harga: total, bayar: pay })
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (data.success && data.transaksi) {
                        showNotaModal(data.transaksi);
                        cart = [];
                        renderCart();
                        document.getElementById('pay-amount').value = '';
                    } else {
                        alert('Gagal: ' + (data.message || 'Terjadi kesalahan sistem'));
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('Terjadi kesalahan saat memproses checkout.');
                });
        }

        function showNotaModal(t) {
            document.getElementById('nota-cabang').innerText = 'Cabang ' + (t.nama_cabang || 'Malang');
            document.getElementById('nota-faktur').innerText = t.no_faktur;
            document.getElementById('nota-tanggal').innerText = t.tanggal;
            document.getElementById('nota-kasir').innerText = t.kasir;
            document.getElementById('nota-total').innerText = 'Rp ' + Number(t.total_transaksi).toLocaleString('id-ID');
            document.getElementById('nota-bayar').innerText = 'Rp ' + Number(t.bayar).toLocaleString('id-ID');
            document.getElementById('nota-kembalian').innerText = 'Rp ' + Number(t.kembalian).toLocaleString('id-ID');

            const itemsContainer = document.getElementById('nota-items');
            itemsContainer.innerHTML = '';
            t.items.forEach(item => {
                itemsContainer.innerHTML += `
                    <div>
                        <div class="flex justify-between font-medium text-slate-800">
                            <span>${item.nama_produk}</span>
                            <span>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</span>
                        </div>
                        <div class="text-[10px] text-slate-500">
                            ${item.jumlah} x Rp ${Number(item.harga_jual).toLocaleString('id-ID')}
                        </div>
                    </div>
                `;
            });

            document.getElementById('modal-nota').classList.remove('hidden');
        }

        function closeNotaModal() {
            document.getElementById('modal-nota').classList.add('hidden');
            location.reload();
        }
    </script>
</body>

</html>
