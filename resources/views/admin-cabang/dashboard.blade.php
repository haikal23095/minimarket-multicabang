<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Cabang {{ $namaCabang }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside id="admin-sidebar"
            class="flex w-full flex-col gap-8 bg-slate-900 p-6 text-white lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:shrink-0 lg:self-start">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">Minimarket</p>
                <h1 class="mt-2 text-2xl font-bold">Admin Cabang</h1>
            </div>

            <nav class="flex flex-wrap gap-2 lg:flex-col">
                <a href="{{ route('admin.cabang.dashboard') }}"
                    class="rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold text-white">
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
            </nav>

            <div class="mt-auto hidden rounded-xl border border-white/10 bg-white/5 p-4 lg:block">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Cabang aktif</p>
                <p class="mt-2 text-lg font-semibold">{{ $namaCabang }}</p>
                <p class="mt-1 font-mono text-xs text-slate-400">{{ $kodeCabang }}</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="true"
                        aria-label="Tutup menu navigasi"
                        class="rounded-lg border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <p class="text-sm text-slate-500">Panel administrasi / {{ $namaCabang }}</p>
                        <h2 class="mt-1 text-2xl font-bold">Dashboard {{ $namaCabang }}</h2>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold">{{ auth()->user()->username }}</p>
                        <p class="text-xs text-slate-500">Admin Cabang</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-8 sm:px-8">
                <section class="rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-sm sm:p-8">
                    <p class="text-sm font-medium text-blue-100">Selamat datang, {{ auth()->user()->username }}</p>
                    <h3 class="mt-2 text-2xl font-bold">Operasional cabang {{ $namaCabang }}</h3>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100">
                        Pantau persediaan barang dan buka halaman pengelolaan stok untuk mencatat stok masuk di cabang ini.
                    </p>
                    <a href="{{ route('admin.stok.index') }}"
                        class="mt-5 inline-flex rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                        Kelola stok cabang
                    </a>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Produk dengan stok</p>
                        <p class="mt-3 text-3xl font-bold">{{ number_format($jumlahProduk, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Jenis produk di cabang {{ $namaCabang }}</p>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total persediaan</p>
                        <p class="mt-3 text-3xl font-bold">{{ number_format($totalStok, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Unit stok tersedia</p>
                    </article>

                    <article class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                        <p class="text-sm font-medium text-amber-800">Produk hampir habis</p>
                        <p class="mt-3 text-3xl font-bold text-amber-900">{{ number_format($produkHampirHabis, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-amber-800">Stok 10 unit atau kurang</p>
                    </article>
                </section>

                <section class="flex flex-col gap-4">
                    <div>
                        <h3 class="text-lg font-bold">Performa penjualan</h3>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan transaksi cabang dalam 7 hari terakhir.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-medium text-slate-500">Omzet 7 hari</p>
                            <p class="mt-3 text-2xl font-bold text-blue-700">
                                Rp {{ number_format($omzetMingguan, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Total nilai transaksi cabang</p>
                        </article>

                        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-medium text-slate-500">Jumlah transaksi 7 hari</p>
                            <p class="mt-3 text-2xl font-bold">
                                {{ number_format($jumlahTransaksiMingguan, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Transaksi berhasil tercatat</p>
                        </article>

                        <article class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                            <p class="text-sm font-medium text-amber-800">Laba</p>
                            <p class="mt-3 text-2xl font-bold text-amber-900">Belum tersedia</p>
                            <p class="mt-1 text-xs text-amber-800">
                                Data harga modal produk belum tersedia untuk menghitung laba.
                            </p>
                        </article>
                    </div>
                </section>

                <section class="grid gap-4 xl:grid-cols-2">
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">Grafik omzet harian</h3>
                                <p class="mt-1 text-sm text-slate-500">Omzet selama 7 hari terakhir</p>
                            </div>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Rupiah</span>
                        </div>

                        <div class="mt-6 flex h-52 items-end gap-2 border-b border-slate-200 pb-2 sm:gap-4"
                            role="img" aria-label="Grafik omzet harian selama 7 hari terakhir">
                            @foreach ($grafikHarian as $dataHarian)
                                <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                    <span class="truncate text-center text-[10px] text-slate-500 sm:text-xs">
                                        {{ number_format($dataHarian['omzet'], 0, ',', '.') }}
                                    </span>
                                    <div class="flex h-32 w-full items-end justify-center">
                                        <div class="w-full max-w-10 rounded-t-md bg-blue-500"
                                            style="height: {{ $dataHarian['tinggi_omzet'] }}%"></div>
                                    </div>
                                    <span class="text-center text-[10px] font-medium text-slate-600 sm:text-xs">
                                        {{ $dataHarian['label'] }}<br>{{ $dataHarian['tanggal'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">Grafik transaksi harian</h3>
                                <p class="mt-1 text-sm text-slate-500">Jumlah transaksi selama 7 hari terakhir</p>
                            </div>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Transaksi</span>
                        </div>

                        <div class="mt-6 flex h-52 items-end gap-2 border-b border-slate-200 pb-2 sm:gap-4"
                            role="img" aria-label="Grafik jumlah transaksi harian selama 7 hari terakhir">
                            @foreach ($grafikHarian as $dataHarian)
                                <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                    <span class="text-xs font-medium text-slate-600">
                                        {{ $dataHarian['jumlah_transaksi'] }}
                                    </span>
                                    <div class="flex h-32 w-full items-end justify-center">
                                        <div class="w-full max-w-10 rounded-t-md bg-indigo-500"
                                            style="height: {{ $dataHarian['tinggi_transaksi'] }}%"></div>
                                    </div>
                                    <span class="text-center text-[10px] font-medium text-slate-600 sm:text-xs">
                                        {{ $dataHarian['label'] }}<br>{{ $dataHarian['tanggal'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold">Perlu mengisi stok?</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Lihat produk yang hampir habis dan catat jumlah barang masuk.
                            </p>
                        </div>
                        <a href="{{ route('admin.stok.index') }}"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Buka manajemen stok
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script>
        document.querySelector('[data-sidebar-toggle]').addEventListener('click', (event) => {
            const button = event.currentTarget;
            const sidebar = document.getElementById(button.getAttribute('aria-controls'));
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            sidebar.style.display = isExpanded ? 'none' : '';
            button.setAttribute('aria-expanded', String(!isExpanded));
            button.setAttribute('aria-label', isExpanded ? 'Buka menu navigasi' : 'Tutup menu navigasi');
        });
    </script>
</body>

</html>
