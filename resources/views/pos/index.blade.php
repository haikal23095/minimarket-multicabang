<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kasir - Cabang Malang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-100 h-screen flex flex-col overflow-hidden">
    <!-- Navbar -->
    <header class="bg-blue-600 text-white px-6 py-3 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <h1 class="text-lg font-bold">Minimarket POS</h1>
            <span class="bg-blue-800 text-xs px-2 py-1 rounded">Cabang Malang</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-medium">Kasir: {{ auth()->user()->username }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded font-semibold">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Main Section -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Panel Produk (Kiri) -->
        <div class="w-2/3 p-4 overflow-y-auto border-r border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-3">Daftar Produk</h2>
            <div class="grid grid-cols-3 gap-4">
                @foreach ($produk as $item)
                    <div onclick="addToCart('{{ $item->id_produk }}', '{{$item->nama_produk }}', {{ $item->harga_jual }}, {{$item->stok->jumlah_stok ?? 0 }})"
                        class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 cursor-pointer hover:border-blue-500 transition duration-150">
                        <span
                            class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">{{ $item->id_produk }}</span>
                        <h3 class="font-bold text-slate-800 mt-2 text-sm">{{ $item->nama_produk }}</h3>
                        <p class="text-blue-600 font-semibold text-sm mt-1">Rp
                            {{ number_format($item->harga_jual, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-500 mt-1">Stok: <span
                                class="font-bold text-slate-700">{{ $item->stok->jumlah_stok ?? 0 }}</span></p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Panel Keranjang & Checkout (Kanan) -->
        <div class="w-1/3 bg-white p-4 flex flex-col justify-between shadow-lg">
            <div>
                <h2 class="text-lg font-semibold text-slate-800 mb-3 border-b pb-2">Keranjang Belanja</h2>
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

                <div class="space-y-2">
                    <label class="block text-xs text-slate-600 font-medium">Uang Pembayaran</label>
                    <input type="number" id="pay-amount" oninput="calculateChange()" placeholder="Masukkan Nominal"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div class="flex justify-between items-center text-sm font-semibold text-slate-600">
                    <span>Kembalian</span>
                    <span id="change-amount" class="text-slate-800">Rp 0</span>
                </div>

                <button onclick="processCheckout()"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition duration-200">
                    Selesaikan Transaksi
                </button>
            </div>
        </div>
    </div>

    <!-- Script Interaksi Kasir -->
    <script>
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
                            <p class="text-xs text-slate-500">Rp ${item.harga_jual.toLocaleString()} x ${item.jumlah}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQty(${index}, -1)" class="px-2 py-0.5 bg-slate-200 rounded font-bold text-xs">-</button>
                            <span class="text-xs font-bold">${item.jumlah}</span>
                            <button onclick="updateQty(${index}, 1)" class="px-2 py-0.5 bg-slate-200 rounded font-bold text-xs">+</button>
                        </div>
                    </div>
                `;
            });

            document.getElementById('total-price').innerText = 'Rp ' + total.toLocaleString();
            calculateChange();
        }

        function updateQty(index, change) {
            cart[index].jumlah += change;
            if (cart[index].jumlah <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function calculateChange() {
            const pay = parseFloat(document.getElementById('pay-amount').value) || 0;
            const change = pay - total;
            document.getElementById('change-amount').innerText = 'Rp ' + (change > 0 ? change.toLocaleString() : 0);
        }

        function processCheckout() {
            const pay = parseFloat(document.getElementById('pay-amount').value) || 0;
            if (cart.length === 0) return alert('Keranjang masih kosong!');
            if (pay < total) return alert('Nominal pembayaran masih kurang!');

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
                    if (data.success) {
                        alert('Transaksi Berhasil! No Faktur: ' + data.no_faktur);
                        cart = [];
                        document.getElementById('pay-amount').value = '';
                        location.reload();
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(err => alert('Terjadi kesalahan sistem.'));
        }
    </script>
</body>

</html>