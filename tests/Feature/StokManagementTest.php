<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_and_branch_admin_can_access_pos_but_head_office_admin_cannot(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $headOfficeAdmin = User::factory()->create(['role' => 'admin_pusat']);

        $this->actingAs($cashier)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertDontSee('Navigasi admin cabang');

        $this->actingAs($branchAdmin)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Navigasi admin cabang')
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee('aria-controls="admin-sidebar"', false)
            ->assertSee('Dashboard')
            ->assertSee('Manajemen Stok')
            ->assertSee('Kasir POS')
            ->assertSee('aria-current="page"', false);

        $this->actingAs($headOfficeAdmin)->get(route('pos.index'))->assertForbidden();
        $this->actingAs($headOfficeAdmin)->post(route('pos.checkout'))->assertForbidden();
    }

    public function test_branch_admin_sees_inventory_for_the_active_branch(): void
    {
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $lowStockProduct = $this->createProductWithStock('PRD-LOW', 5, 'MLG');
        $normalStockProduct = $this->createProductWithStock('PRD-NORMAL', 11, 'MLG');
        $surabayaProduct = $this->createProductWithStock('PRD-SBY', 2, 'SBY');

        $response = $this->actingAs($branchAdmin)->get(route('admin.stok.index'));

        $response->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertViewHas('jumlahProduk', 2)
            ->assertViewHas('totalStok', 16)
            ->assertViewHas('produkHampirHabis', 1)
            ->assertSee($lowStockProduct->nama_produk)
            ->assertSee($normalStockProduct->nama_produk)
            ->assertDontSee($surabayaProduct->nama_produk)
            ->assertSee('5 unit')
            ->assertSee('Produk hampir habis');

        $this->actingAs($branchAdmin)
            ->get(route('admin.cabang.dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle', false)
            ->assertSee('Dashboard Malang')
            ->assertSee('MLG');
    }

    public function test_branch_admin_can_filter_inventory_by_status_and_search(): void
    {
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $lowStockProduct = $this->createProductWithStock('PRD-LOW', 5, 'MLG');
        $normalStockProduct = $this->createProductWithStock('PRD-NORMAL', 11, 'MLG');

        $response = $this->actingAs($branchAdmin)
            ->get(route('admin.stok.index', ['status' => 'low', 'search' => 'PRD-LOW']));

        $response->assertOk()
            ->assertSee($lowStockProduct->nama_produk)
            ->assertDontSee($normalStockProduct->nama_produk)
            ->assertSee('name="status"', false)
            ->assertSee('value="PRD-LOW"', false);
    }

    public function test_branch_admin_can_add_incoming_stock_to_malang_inventory(): void
    {
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $product = $this->createProductWithStock('PRD-ADD', 5, 'MLG');

        $response = $this->actingAs($branchAdmin)->post(route('admin.stok.store'), [
            'id_produk' => $product->id_produk,
            'jumlah_masuk' => 7,
        ]);

        $response->assertRedirect(route('admin.stok.index'))
            ->assertSessionHas('status', 'Stok barang berhasil ditambahkan.');

        $this->assertDatabaseHas('Stok', [
            'id_cabang' => 'MLG',
            'id_produk' => $product->id_produk,
            'jumlah_stok' => 12,
        ]);
    }

    public function test_rejects_non_positive_incoming_stock_without_changing_inventory(): void
    {
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $product = $this->createProductWithStock('PRD-INVALID', 5, 'MLG');

        $response = $this->actingAs($branchAdmin)->from(route('admin.stok.index'))->post(route('admin.stok.store'), [
            'id_produk' => $product->id_produk,
            'jumlah_masuk' => 0,
        ]);

        $response->assertRedirect(route('admin.stok.index'))
            ->assertSessionHasErrors('jumlah_masuk');

        $this->assertDatabaseHas('Stok', [
            'id_cabang' => 'MLG',
            'id_produk' => $product->id_produk,
            'jumlah_stok' => 5,
        ]);
    }

    public function test_non_branch_admin_cannot_access_stock_management(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($cashier)->get(route('admin.stok.index'))->assertForbidden();
    }

    public function test_admin_dashboard_and_stock_management_follow_the_surabaya_node(): void
    {
        config(['app.node' => 'surabaya']);
        config(['app.branch' => ['id' => 'SBY', 'name' => 'Surabaya']]);
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $product = $this->createProductWithStock('PRD-SBY', 4, 'SBY');

        $this->actingAs($branchAdmin)
            ->get(route('admin.cabang.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Surabaya')
            ->assertSee('SBY')
            ->assertSee('4', false);

        $this->actingAs($branchAdmin)
            ->get(route('admin.stok.index'))
            ->assertOk()
            ->assertSee('Cabang Surabaya')
            ->assertSee($product->nama_produk);

        $this->actingAs($branchAdmin)->post(route('admin.stok.store'), [
            'id_produk' => $product->id_produk,
            'jumlah_masuk' => 3,
        ])->assertRedirect(route('admin.stok.index'));

        $this->assertDatabaseHas('Stok', [
            'id_cabang' => 'SBY',
            'id_produk' => $product->id_produk,
            'jumlah_stok' => 7,
        ]);
    }

    public function test_branch_dashboard_shows_seven_day_sales_without_other_branch_transactions(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 28)->setTime(12, 0));
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $pegawai = Pegawai::factory()->create();
        Cabang::query()->firstOrCreate(
            ['id_cabang' => 'MLG'],
            ['nama_cabang' => 'Minimarket Malang', 'alamat' => 'Malang'],
        );
        Cabang::query()->firstOrCreate(
            ['id_cabang' => 'SBY'],
            ['nama_cabang' => 'Minimarket Surabaya', 'alamat' => 'Surabaya'],
        );

        Transaksi::factory()->create([
            'id_cabang' => 'MLG',
            'id_pegawai' => $pegawai->id_pegawai,
            'tanggal_transaksi' => now()->subDays(2),
            'total_transaksi' => 100000,
        ]);
        Transaksi::factory()->create([
            'id_cabang' => 'MLG',
            'id_pegawai' => $pegawai->id_pegawai,
            'tanggal_transaksi' => now()->subDays(2),
            'total_transaksi' => 50000,
        ]);
        Transaksi::factory()->create([
            'id_cabang' => 'SBY',
            'id_pegawai' => $pegawai->id_pegawai,
            'tanggal_transaksi' => now()->subDays(2),
            'total_transaksi' => 999999,
        ]);
        Transaksi::factory()->create([
            'id_cabang' => 'MLG',
            'id_pegawai' => $pegawai->id_pegawai,
            'tanggal_transaksi' => now()->subDays(8),
            'total_transaksi' => 888888,
        ]);

        $response = $this->actingAs($branchAdmin)->get(route('admin.cabang.dashboard'));

        $response->assertOk()
            ->assertViewHas('jumlahTransaksiMingguan', 2)
            ->assertViewHas('omzetMingguan', 150000.0)
            ->assertViewHas('grafikHarian', fn (array $grafikHarian): bool => count($grafikHarian) === 7
                && $grafikHarian[4]['jumlah_transaksi'] === 2
                && $grafikHarian[4]['omzet'] === 150000.0)
            ->assertSee('Omzet 7 hari')
            ->assertSee('Rp 150.000')
            ->assertSee('Jumlah transaksi 7 hari')
            ->assertSee('Belum tersedia')
            ->assertSee('Data harga modal produk belum tersedia')
            ->assertDontSee('999.999')
            ->assertDontSee('888.888');
    }

    public function test_branch_admin_login_redirects_to_branch_dashboard(): void
    {
        $branchAdmin = User::factory()->create([
            'username' => 'admin_mlg_dashboard',
            'role' => 'admin_cabang',
        ]);

        $this->post('/login', [
            'username' => $branchAdmin->username,
            'password' => 'password',
        ])->assertRedirect(route('admin.cabang.dashboard'));
    }

    private function createProductWithStock(string $productId, int $quantity, string $branchId): Produk
    {
        Cabang::query()->firstOrCreate(
            ['id_cabang' => $branchId],
            [
                'nama_cabang' => $branchId === 'SBY' ? 'Minimarket Surabaya' : 'Minimarket Malang',
                'alamat' => $branchId === 'SBY' ? 'Surabaya' : 'Malang',
            ],
        );

        $product = Produk::factory()->create(['id_produk' => $productId]);

        Stok::factory()->create([
            'id_cabang' => $branchId,
            'id_produk' => $product->id_produk,
            'jumlah_stok' => $quantity,
        ]);

        return $product;
    }
}
