@extends('admin-pusat.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard Pusat')

@section('content')
    <section class="rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-700 p-6 text-white shadow-sm sm:p-8">
        <p class="text-sm font-medium text-blue-100">Selamat datang, {{ auth()->user()->username }}</p>
        <h3 class="mt-2 text-2xl font-bold">Kelola master data minimarket</h3>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100">
            Perubahan produk dan kategori pada node pusat menjadi sumber master data untuk seluruh cabang.
        </p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2" aria-label="Ringkasan master data">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Produk terdaftar</p>
            <p class="mt-3 text-3xl font-bold">{{ number_format($jumlahProduk, 0, ',', '.') }}</p>
            <a href="{{ route('admin.pusat.produk.index') }}"
                class="mt-4 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">
                Kelola produk
            </a>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Kategori terdaftar</p>
            <p class="mt-3 text-3xl font-bold">{{ number_format($jumlahKategori, 0, ',', '.') }}</p>
            <a href="{{ route('admin.pusat.kategori.index') }}"
                class="mt-4 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">
                Kelola kategori
            </a>
        </article>
    </section>

    <section class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-sm leading-6 text-blue-900">
        Akses perubahan master data dibatasi pada admin_pusat di node pusat. Node cabang tetap menggunakan salinan data hasil replikasi.
    </section>
@endsection
