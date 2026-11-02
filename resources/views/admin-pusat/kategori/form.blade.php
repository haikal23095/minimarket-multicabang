@extends('admin-pusat.layout')

@section('title', $kategori ? 'Edit Kategori' : 'Tambah Kategori')
@section('heading', $kategori ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
    <section class="max-w-3xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <form
            action="{{ $kategori ? route('admin.pusat.kategori.update', $kategori) : route('admin.pusat.kategori.store') }}"
            method="POST" class="flex flex-col gap-5">
            @csrf
            @if ($kategori)
                @method('PUT')
                <div>
                    <p class="mb-1 text-sm font-semibold text-slate-700">ID Kategori</p>
                    <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm">{{ $kategori->id_kategori }}</p>
                </div>
            @else
                <div>
                    <label for="id_kategori" class="mb-1 block text-sm font-semibold text-slate-700">ID Kategori</label>
                    <input id="id_kategori" name="id_kategori" value="{{ old('id_kategori') }}" maxlength="20" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-slate-500">Maksimal 20 karakter dan harus unik, contoh KTG003.</p>
                </div>
            @endif

            <div>
                <label for="nama_kategori" class="mb-1 block text-sm font-semibold text-slate-700">Nama Kategori</label>
                <input id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategori?->nama_kategori) }}"
                    maxlength="100" required
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route('admin.pusat.kategori.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </section>
@endsection
