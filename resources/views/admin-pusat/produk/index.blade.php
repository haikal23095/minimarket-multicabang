@extends('admin-pusat.layout')

@section('title', 'Master Produk')
@section('heading', 'Master Produk')

@section('content')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold">Daftar produk</h3>
                <p class="mt-1 text-sm text-slate-500">Data ini dikelola di pusat dan dipasok ke cabang melalui replikasi.</p>
            </div>
            <a href="{{ route('admin.pusat.produk.create') }}"
                class="inline-flex justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                Tambah produk
            </a>
        </div>

        @if ($produk->isEmpty())
            <p class="p-6 text-sm text-slate-500">Belum ada produk terdaftar.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">ID Produk</th>
                            <th scope="col" class="px-5 py-3">Nama Produk</th>
                            <th scope="col" class="px-5 py-3">Kategori</th>
                            <th scope="col" class="px-5 py-3 text-right">Harga Jual</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($produk as $item)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs">{{ $item->id_produk }}</td>
                                <td class="px-5 py-4 font-medium">{{ $item->nama_produk }}</td>
                                <td class="px-5 py-4">{{ $item->kategori?->nama_kategori ?? 'Kategori tidak tersedia' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">Rp {{ number_format((float) $item->harga_jual, 2, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.pusat.produk.edit', $item) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.pusat.produk.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 px-3 py-1.5 font-semibold text-red-700 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $produk->links() }}
            </div>
        @endif
    </section>
@endsection
