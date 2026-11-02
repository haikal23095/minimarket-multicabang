<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\View\View;

class AdminPusatController extends Controller
{
    public function index(): View
    {
        return view('admin-pusat.dashboard', [
            'jumlahProduk' => Produk::query()->count(),
            'jumlahKategori' => Kategori::query()->count(),
        ]);
    }
}
