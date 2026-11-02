@extends('admin-pusat.layout')

@section('title', 'Master Kategori')
@section('heading', 'Master Kategori')

@section('content')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold">Daftar kategori</h3>
                <p class="mt-1 text-sm text-slate-500">Kategori yang digunakan produk tidak dapat dihapus.</p>
            </div>
            <a href="{{ route('admin.pusat.kategori.create') }}"
                class="inline-flex justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                Tambah kategori
            </a>
        </div>

        @if ($kategori->isEmpty())
            <p class="p-6 text-sm text-slate-500">Belum ada kategori terdaftar.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">ID Kategori</th>
                            <th scope="col" class="px-5 py-3">Nama Kategori</th>
                            <th scope="col" class="px-5 py-3 text-right">Jumlah Produk</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($kategori as $item)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs">{{ $item->id_kategori }}</td>
                                <td class="px-5 py-4 font-medium">{{ $item->nama_kategori }}</td>
                                <td class="px-5 py-4 text-right">{{ number_format($item->produk_count, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.pusat.kategori.edit', $item) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.pusat.kategori.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Hapus kategori ini?')">
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
                {{ $kategori->links() }}
            </div>
        @endif
    </section>
@endsection
