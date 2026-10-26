<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok - Cabang {{ $namaCabang }}</title>
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

            <nav class="flex flex-wrap gap-2 lg:flex-col" aria-label="Navigasi admin cabang">
                <a href="{{ route('admin.cabang.dashboard') }}"
                    class="rounded-lg px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
                    Dashboard
                </a>
                <a href="{{ route('admin.stok.index') }}" aria-current="page"
                    class="rounded-lg bg-white/10 px-4 py-3 text-sm font-semibold text-white">
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
                        <h2 class="mt-1 text-2xl font-bold">Manajemen Stok</h2>
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
                    <p class="text-sm font-medium text-blue-100">Operasional cabang {{ $namaCabang }}</p>
                    <h3 class="mt-2 text-2xl font-bold">Kelola persediaan barang</h3>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100">
                        Pantau stok seluruh produk dan catat barang masuk. Perubahan hanya diterapkan ke inventaris cabang ini.
                    </p>
                </section>

                @if (session('status'))
                    <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Ringkasan stok">
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Produk terdaftar</p>
                        <p class="mt-3 text-3xl font-bold">{{ number_format($jumlahProduk, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Produk dengan catatan stok di {{ $namaCabang }}</p>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total persediaan</p>
                        <p class="mt-3 text-3xl font-bold">{{ number_format($totalStok, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Unit barang tersedia</p>
                    </article>

                    <article class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                        <p class="text-sm font-medium text-amber-800">Produk hampir habis</p>
                        <p class="mt-3 text-3xl font-bold text-amber-900">
                            {{ number_format($produkHampirHabis, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs text-amber-800">Stok {{ $batasStokRendah }} unit atau kurang</p>
                    </article>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h3 class="font-semibold">Daftar persediaan</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Cari produk, filter kondisi stok, atau catat jumlah barang masuk.
                            </p>
                        </div>

                        <form action="{{ route('admin.stok.index') }}" method="GET"
                            class="grid gap-3 sm:grid-cols-[minmax(12rem,1fr)_12rem_auto]">
                            <div>
                                <label for="search" class="sr-only">Cari produk</label>
                                <input id="search" name="search" type="search" value="{{ $search }}"
                                    placeholder="Cari nama atau kode produk"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="status" class="sr-only">Filter kondisi stok</label>
                                <select id="status" name="status"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="all" @selected($status === 'all')>Semua kondisi</option>
                                    <option value="low" @selected($status === 'low')>Hampir habis</option>
                                    <option value="normal" @selected($status === 'normal')>Stok aman</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                Terapkan
                            </button>
                        </form>
                    </div>

                    @if ($stok->isEmpty())
                        <div class="flex flex-col items-center gap-2 px-5 py-12 text-center">
                            <p class="font-semibold text-slate-700">Tidak ada produk yang cocok</p>
                            <p class="text-sm text-slate-500">Ubah kata kunci atau filter untuk melihat persediaan.</p>
                            @if ($search !== '' || $status !== 'all')
                                <a href="{{ route('admin.stok.index') }}"
                                    class="mt-2 text-sm font-semibold text-blue-700 hover:text-blue-800">
                                    Hapus filter
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th scope="col" class="px-5 py-3">Produk</th>
                                        <th scope="col" class="px-5 py-3">Stok tersedia</th>
                                        <th scope="col" class="px-5 py-3">Kondisi</th>
                                        <th scope="col" class="px-5 py-3">Catat barang masuk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($stok as $itemStok)
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-slate-800">{{ $itemStok->nama_produk }}</p>
                                                <p class="mt-1 font-mono text-xs text-slate-500">{{ $itemStok->id_produk }}</p>
                                            </td>
                                            <td class="px-5 py-4 font-semibold">
                                                {{ number_format($itemStok->jumlah_stok, 0, ',', '.') }} unit
                                            </td>
                                            <td class="px-5 py-4">
                                                @if ($itemStok->jumlah_stok <= $batasStokRendah)
                                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                        Hampir habis
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                                        Aman
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4">
                                                <form action="{{ route('admin.stok.store') }}" method="POST"
                                                    class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="id_produk" value="{{ $itemStok->id_produk }}">
                                                    <label class="sr-only" for="jumlah-masuk-{{ $itemStok->id_produk }}">
                                                        Jumlah stok masuk untuk {{ $itemStok->nama_produk }}
                                                    </label>
                                                    <input id="jumlah-masuk-{{ $itemStok->id_produk }}" name="jumlah_masuk"
                                                        type="number" min="1" max="1000000" required
                                                        value="{{ old('id_produk') === $itemStok->id_produk ? old('jumlah_masuk') : 1 }}"
                                                        class="w-24 rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                                    <button type="submit"
                                                        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                                                        Tambah
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($stok->hasPages())
                            <div class="border-t border-slate-200 px-5 py-4">
                                {{ $stok->links() }}
                            </div>
                        @endif
                    @endif
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
