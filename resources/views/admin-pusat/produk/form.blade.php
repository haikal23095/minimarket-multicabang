@extends('admin-pusat.layout')

@section('title', $produk ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $produk ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <section class="max-w-3xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <form
            action="{{ $produk ? route('admin.pusat.produk.update', $produk) : route('admin.pusat.produk.store') }}"
            method="POST" class="flex flex-col gap-5">
            @csrf
            @if ($produk)
                @method('PUT')
                <div>
                    <p class="mb-1 text-sm font-semibold text-slate-700">ID Produk</p>
                    <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm">{{ $produk->id_produk }}</p>
                </div>
            @else
                <div>
                    <label for="id_produk" class="mb-1 block text-sm font-semibold text-slate-700">ID Produk</label>
                    <input id="id_produk" name="id_produk" value="{{ old('id_produk') }}" maxlength="20" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-slate-500">Maksimal 20 karakter dan harus unik, contoh PRD004.</p>
                </div>
            @endif

            <div>
                <label for="nama_produk" class="mb-1 block text-sm font-semibold text-slate-700">Nama Produk</label>
                <input id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk?->nama_produk) }}"
                    maxlength="150" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div>
                <label for="id_kategori" class="mb-1 block text-sm font-semibold text-slate-700">Kategori</label>
                <select id="id_kategori" name="id_kategori" required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Pilih kategori</option>
                    @foreach ($kategori as $item)
                        <option value="{{ $item->id_kategori }}"
                            @selected(old('id_kategori', $produk?->id_kategori) === $item->id_kategori)>
                            {{ $item->nama_kategori }} ({{ $item->id_kategori }})
                        </option>
                    @endforeach
                </select>
                @if ($kategori->isEmpty())
                    <p class="mt-2 text-sm text-amber-700">
                        Belum ada kategori.
                        <a href="{{ route('admin.pusat.kategori.create') }}" class="font-semibold underline">Tambah kategori dahulu</a>.
                    </p>
                @endif
            </div>

            <div>
                <label for="harga_jual" class="mb-1 block text-sm font-semibold text-slate-700">Harga Jual (Rp)</label>
                <input id="harga_jual" name="harga_jual" type="number" min="0.01" max="9999999999.99" step="0.01"
                    value="{{ old('harga_jual', $produk?->harga_jual) }}" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route('admin.pusat.produk.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" @disabled($kategori->isEmpty())
                    class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Simpan Produk
                </button>
            </div>
        </form>
    </section>
@endsection
