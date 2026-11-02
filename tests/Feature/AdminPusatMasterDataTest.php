<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPusatMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pusat_can_view_dashboard_and_master_data_lists(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create(['nama_kategori' => 'Minuman']);
        Produk::factory()->create([
            'id_produk' => 'PRD001',
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Air Mineral',
        ]);

        $this->actingAs($adminPusat)
            ->get(route('admin.pusat.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pusat')
            ->assertSee('1');

        $this->actingAs($adminPusat)
            ->get(route('admin.pusat.produk.index'))
            ->assertOk()
            ->assertSee('Air Mineral');

        $this->actingAs($adminPusat)
            ->get(route('admin.pusat.kategori.index'))
            ->assertOk()
            ->assertSee('Minuman');
    }

    #[DataProvider('masterDataWriteRoutes')]
    public function test_malang_node_forbids_master_data_forms_and_mutations(
        string $method,
        string $routeName,
        string $resource,
    ): void {
        config(['app.node' => 'malang']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);
        $record = $resource === 'produk' ? $produk : $kategori;
        $routeParameters = in_array($method, ['PUT', 'DELETE'], true)
            || str_ends_with($routeName, '.edit')
            ? [$resource => $record]
            : [];
        $uri = route($routeName, $routeParameters);

        $this->actingAs($adminPusat)
            ->call($method, $uri, [])
            ->assertForbidden();

        $this->assertModelExists($produk);
        $this->assertModelExists($kategori);
    }

    public static function masterDataWriteRoutes(): array
    {
        return [
            'product create form' => ['GET', 'admin.pusat.produk.create', 'produk'],
            'product create action' => ['POST', 'admin.pusat.produk.store', 'produk'],
            'product edit form' => ['GET', 'admin.pusat.produk.edit', 'produk'],
            'product update action' => ['PUT', 'admin.pusat.produk.update', 'produk'],
            'product delete action' => ['DELETE', 'admin.pusat.produk.destroy', 'produk'],
            'category create form' => ['GET', 'admin.pusat.kategori.create', 'kategori'],
            'category create action' => ['POST', 'admin.pusat.kategori.store', 'kategori'],
            'category edit form' => ['GET', 'admin.pusat.kategori.edit', 'kategori'],
            'category update action' => ['PUT', 'admin.pusat.kategori.update', 'kategori'],
            'category delete action' => ['DELETE', 'admin.pusat.kategori.destroy', 'kategori'],
        ];
    }

    public function test_admin_pusat_can_create_a_product_on_the_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();

        $this->actingAs($adminPusat)
            ->post(route('admin.pusat.produk.store'), [
                'id_produk' => 'PRD-NEW',
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Teh Botol',
                'harga_jual' => '5000.00',
            ])
            ->assertRedirect(route('admin.pusat.produk.index'))
            ->assertSessionHas('status', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('Produk', [
            'id_produk' => 'PRD-NEW',
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Teh Botol',
            'harga_jual' => '5000.00',
        ]);
    }

    public function test_branch_admin_cannot_create_master_data_on_the_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $branchAdmin = User::factory()->create(['role' => 'admin_cabang']);
        $kategori = Kategori::factory()->create();

        $this->actingAs($branchAdmin)
            ->post(route('admin.pusat.produk.store'), [
                'id_produk' => 'PRD-BLOCKED',
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Produk Tidak Diizinkan',
                'harga_jual' => '5000.00',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('Produk', ['id_produk' => 'PRD-BLOCKED']);
    }

    public function test_product_id_with_route_unsafe_characters_is_rejected(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();

        $this->actingAs($adminPusat)
            ->from(route('admin.pusat.produk.create'))
            ->post(route('admin.pusat.produk.store'), [
                'id_produk' => 'PRD/NEW',
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Teh Botol',
                'harga_jual' => '5000.00',
            ])
            ->assertRedirect(route('admin.pusat.produk.create'))
            ->assertSessionHasErrors('id_produk');

        $this->assertDatabaseMissing('Produk', ['id_produk' => 'PRD/NEW']);
    }

    public function test_admin_pusat_can_update_a_product_on_the_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);

        $this->actingAs($adminPusat)
            ->put(route('admin.pusat.produk.update', $produk), [
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Nama Baru',
                'harga_jual' => '12500.00',
            ])
            ->assertRedirect(route('admin.pusat.produk.index'))
            ->assertSessionHas('status', 'Produk berhasil diperbarui.');

        $this->assertDatabaseHas('Produk', [
            'id_produk' => $produk->id_produk,
            'nama_produk' => 'Nama Baru',
            'harga_jual' => '12500.00',
        ]);
    }

    public function test_admin_pusat_can_manage_categories_on_the_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);

        $this->actingAs($adminPusat)
            ->post(route('admin.pusat.kategori.store'), [
                'id_kategori' => 'KTG-NEW',
                'nama_kategori' => 'Minuman',
            ])
            ->assertRedirect(route('admin.pusat.kategori.index'))
            ->assertSessionHas('status', 'Kategori berhasil ditambahkan.');

        $kategori = Kategori::query()->findOrFail('KTG-NEW');

        $this->actingAs($adminPusat)
            ->put(route('admin.pusat.kategori.update', $kategori), [
                'nama_kategori' => 'Minuman Dingin',
            ])
            ->assertRedirect(route('admin.pusat.kategori.index'))
            ->assertSessionHas('status', 'Kategori berhasil diperbarui.');

        $this->assertDatabaseHas('Kategori', [
            'id_kategori' => 'KTG-NEW',
            'nama_kategori' => 'Minuman Dingin',
        ]);

        $this->actingAs($adminPusat)
            ->delete(route('admin.pusat.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.pusat.kategori.index'))
            ->assertSessionHas('status', 'Kategori berhasil dihapus.');

        $this->assertModelMissing($kategori);
    }

    public function test_admin_pusat_cannot_delete_a_category_that_is_used_by_products(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);

        $this->actingAs($adminPusat)
            ->delete(route('admin.pusat.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.pusat.kategori.index'))
            ->assertSessionHas('error', 'Kategori yang masih digunakan produk tidak dapat dihapus.');

        $this->assertModelExists($kategori);
        $this->assertModelExists($produk);
    }

    public function test_admin_pusat_can_delete_an_unused_product_on_the_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);

        $this->actingAs($adminPusat)
            ->delete(route('admin.pusat.produk.destroy', $produk))
            ->assertRedirect(route('admin.pusat.produk.index'))
            ->assertSessionHas('status', 'Produk berhasil dihapus.');

        $this->assertModelMissing($produk);
    }

    public function test_admin_pusat_cannot_delete_a_product_that_has_local_stock(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);
        Cabang::query()->create([
            'id_cabang' => 'MLG',
            'nama_cabang' => 'Minimarket Malang',
            'alamat' => 'Malang',
        ]);
        Stok::factory()->create([
            'id_cabang' => 'MLG',
            'id_produk' => $produk->id_produk,
            'jumlah_stok' => 4,
        ]);

        $this->actingAs($adminPusat)
            ->delete(route('admin.pusat.produk.destroy', $produk))
            ->assertRedirect(route('admin.pusat.produk.index'))
            ->assertSessionHas('error', 'Produk dengan stok atau riwayat transaksi tidak dapat dihapus.');

        $this->assertModelExists($produk);
        $this->assertDatabaseHas('Stok', [
            'id_cabang' => 'MLG',
            'id_produk' => $produk->id_produk,
            'jumlah_stok' => 4,
        ]);
    }

    public function test_admin_pusat_cannot_delete_a_product_with_transaction_history(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create(['role' => 'admin_pusat']);
        $kategori = Kategori::factory()->create();
        $produk = Produk::factory()->create(['id_kategori' => $kategori->id_kategori]);
        Cabang::query()->create([
            'id_cabang' => 'MLG',
            'nama_cabang' => 'Minimarket Malang',
            'alamat' => 'Malang',
        ]);
        $transaksi = Transaksi::factory()->create(['id_cabang' => 'MLG']);
        DB::table('DetailTransaksi')->insert([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_produk' => $produk->id_produk,
            'qty' => 1,
            'harga' => 5000,
            'subtotal' => 5000,
        ]);

        $this->actingAs($adminPusat)
            ->delete(route('admin.pusat.produk.destroy', $produk))
            ->assertRedirect(route('admin.pusat.produk.index'))
            ->assertSessionHas('error', 'Produk dengan stok atau riwayat transaksi tidak dapat dihapus.');

        $this->assertModelExists($produk);
        $this->assertDatabaseHas('DetailTransaksi', [
            'id_transaksi' => $transaksi->id_transaksi,
            'id_produk' => $produk->id_produk,
        ]);
    }

    public function test_guest_is_redirected_away_from_admin_pusat_pages(): void
    {
        config(['app.node' => 'pusat']);

        $this->get(route('admin.pusat.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_pusat_login_redirects_to_the_admin_pusat_dashboard(): void
    {
        config(['app.node' => 'pusat']);
        $adminPusat = User::factory()->create([
            'username' => 'admin_pusat_login',
            'password' => bcrypt('password'),
            'role' => 'admin_pusat',
        ]);

        $this->post(route('login'), [
            'username' => $adminPusat->username,
            'password' => 'password',
        ])->assertRedirect(route('admin.pusat.dashboard'));
    }
}
