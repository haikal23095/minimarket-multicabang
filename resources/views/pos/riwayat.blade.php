<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Cabang {{ $namaCabang }}</title>
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

<body class="min-h-screen bg-slate-100 text-slate-800">
    @if (auth()->user()->role === 'admin_cabang')
        <div class="min-h-screen lg:flex">
            <aside id="admin-sidebar"
                class="flex w-full flex-col gap-8 bg-slate-900 p-6 text-white lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:shrink-0 lg:self-start">
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
                    <a href="{{ route('pos.index') }}"
                        class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
                        Kasir POS
                    </a>
                    <a href="{{ route('pos.riwayat') }}" aria-current="page"
                        class="rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold text-white">
                        Riwayat Transaksi
                    </a>
                </nav>

                <div class="mt-auto hidden rounded-xl border border-white/10 bg-white/5 p-4 lg:block">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Cabang aktif</p>
                    <p class="mt-2 text-lg font-semibold">{{ $namaCabang }}</p>
                    <p class="mt-1 font-mono text-xs text-slate-400">{{ $kodeCabang }}</p>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
    @else
        <div class="min-h-screen flex flex-col">
            <header class="bg-blue-600 text-white px-6 py-3 flex justify-between items-center shadow-md">
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-bold">Minimarket POS</h1>
                    <span class="bg-blue-800 text-xs px-2 py-1 rounded">
                        Cabang {{ $namaCabang }}
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('pos.index') }}"
                        class="flex items-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white text-xs px-3 py-1.5 rounded font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Kasir
                    </a>
                    <span class="text-sm font-medium">Kasir: {{ auth()->user()->username }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </header>
            <main class="flex-1">
    @endif

            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    @if (auth()->user()->role === 'admin_cabang')
                        <button type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="true"
                            aria-label="Tutup menu navigasi"
                            class="rounded-lg border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 lg:hidden">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    @endif
                    <div>
                        <p class="text-sm text-slate-500">Operasional / {{ $namaCabang }}</p>
                        <h2 class="mt-1 text-2xl font-bold">Riwayat Transaksi Penjualan</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('pos.index') }}"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Buka POS Kasir
                    </a>
                </div>
            </header>

            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-8 sm:px-8">
                <!-- Stat Cards -->
                <section class="grid gap-4 sm:grid-cols-2" aria-label="Ringkasan transaksi">
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Transaksi Hari Ini</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($ringkasan['transaksi_hari_ini']) }}</p>
                        <p class="mt-1 text-xs text-slate-500">Jumlah struk belanja diterbitkan hari ini</p>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Omzet Hari Ini</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">Rp {{ number_format($ringkasan['omzet_hari_ini'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Total penerimaan penjualan cabang hari ini</p>
                    </article>
                </section>

                <!-- Filter & Search Bar -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <form action="{{ route('pos.riwayat') }}" method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto_auto_auto]">
                        <div>
                            <label for="search" class="sr-only">Cari No Faktur</label>
                            <input id="search" name="search" type="search" value="{{ $search }}"
                                placeholder="Cari berdasarkan No Faktur (cth: FKT-MLG-...)"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                        <div>
                            <label for="tanggal" class="sr-only">Filter Tanggal</label>
                            <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                        <button type="submit"
                            class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                            Filter
                        </button>
                        @if ($search !== '' || $tanggal !== '')
                            <a href="{{ route('pos.riwayat') }}"
                                class="flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Table Daftar Transaksi -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    @if ($transaksi->isEmpty())
                        <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                            <p class="font-semibold text-slate-700">Belum ada riwayat transaksi</p>
                            <p class="text-sm text-slate-500">Transaksi yang diproses di kasir akan muncul di sini.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th scope="col" class="px-5 py-3.5">No Faktur</th>
                                        <th scope="col" class="px-5 py-3.5">Waktu Transaksi</th>
                                        <th scope="col" class="px-5 py-3.5">Kasir</th>
                                        <th scope="col" class="px-5 py-3.5">Item</th>
                                        <th scope="col" class="px-5 py-3.5 text-right">Total Transaksi</th>
                                        <th scope="col" class="px-5 py-3.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($transaksi as $t)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                                                <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md border border-blue-200 text-xs font-mono">
                                                    {{ $t->no_faktur }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-slate-600">
                                                {{ \Carbon\Carbon::parse($t->tanggal_transaksi)->translatedFormat('d M Y, H:i') }}
                                            </td>
                                            <td class="px-5 py-4 text-slate-700 font-medium">
                                                {{ $t->pegawai->nama_pegawai ?? 'Kasir' }}
                                            </td>
                                            <td class="px-5 py-4 text-slate-500 text-xs">
                                                {{ $t->details->sum('qty') }} barang
                                            </td>
                                            <td class="px-5 py-4 text-right font-bold text-slate-900">
                                                Rp {{ number_format($t->total_transaksi, 0, ',', '.') }}
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <button onclick="viewNota('{{ $t->id_transaksi }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Lihat Nota
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-slate-200 p-4">
                            {{ $transaksi->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nota / Struk Pembayaran -->
    <div id="modal-nota" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 transform transition-all">
            <!-- Header Modal (Non-print) -->
            <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex justify-between items-center print:hidden">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h3 class="font-bold text-slate-800 text-sm">Detail Nota Penjualan</h3>
                </div>
                <button onclick="closeNotaModal()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Area Struk Nota Fisik -->
            <div id="receipt-print-area" class="p-6 font-mono text-xs text-slate-800 bg-white">
                <div class="text-center pb-3 border-b border-dashed border-slate-300 space-y-1">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">MINIMARKET MULTI-CABANG</h2>
                    <p id="nota-cabang" class="font-semibold text-slate-600">Cabang {{ $namaCabang }}</p>
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
                    class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function viewNota(idTransaksi) {
            fetch('/pos/nota/' + idTransaksi)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('nota-cabang').innerText = 'Cabang ' + (data.nama_cabang || 'Malang');
                    document.getElementById('nota-faktur').innerText = data.no_faktur;
                    document.getElementById('nota-tanggal').innerText = data.tanggal;
                    document.getElementById('nota-kasir').innerText = data.kasir;
                    document.getElementById('nota-total').innerText = 'Rp ' + Number(data.total_transaksi).toLocaleString('id-ID');
                    document.getElementById('nota-bayar').innerText = 'Rp ' + Number(data.bayar).toLocaleString('id-ID');
                    document.getElementById('nota-kembalian').innerText = 'Rp ' + Number(data.kembalian).toLocaleString('id-ID');

                    const itemsContainer = document.getElementById('nota-items');
                    itemsContainer.innerHTML = '';
                    data.items.forEach(item => {
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
                })
                .catch(err => alert('Gagal memuat nota: ' + err));
        }

        function closeNotaModal() {
            document.getElementById('modal-nota').classList.add('hidden');
        }
    </script>
</body>

</html>
