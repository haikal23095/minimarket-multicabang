<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnsureMasterDataWriteAccessTest extends TestCase
{
    #[DataProvider('deniedNodeAndRoleCombinations')]
    public function test_rejects_master_data_writes_outside_the_head_office_admin(
        string $node,
        string $role,
    ): void {
        config(['app.node' => $node]);
        $this->registerMasterDataWriteRoute();

        $this->actingAs(User::factory()->make(['role' => $role]))
            ->post('/test/master-data-write')
            ->assertForbidden();
    }

    public static function deniedNodeAndRoleCombinations(): array
    {
        return [
            'Malang branch admin' => ['malang', 'admin_cabang'],
            'Malang cashier' => ['malang', 'kasir'],
            'Malang head-office admin' => ['malang', 'admin_pusat'],
            'Surabaya branch admin' => ['surabaya', 'admin_cabang'],
            'Surabaya head-office admin' => ['surabaya', 'admin_pusat'],
            'Pusat branch admin' => ['pusat', 'admin_cabang'],
        ];
    }

    public function test_allows_master_data_writes_for_head_office_admin_on_pusat_node(): void
    {
        config(['app.node' => 'pusat']);
        $this->registerMasterDataWriteRoute();

        $this->actingAs(User::factory()->make(['role' => 'admin_pusat']))
            ->post('/test/master-data-write')
            ->assertOk()
            ->assertSee('allowed');
    }

    public function test_unauthenticated_requests_cannot_write_master_data(): void
    {
        config(['app.node' => 'pusat']);
        $this->registerMasterDataWriteRoute();

        $this->post('/test/master-data-write')
            ->assertRedirect(route('login'));
    }

    private function registerMasterDataWriteRoute(): void
    {
        Route::middleware(['auth', 'master-data.write'])
            ->post('/test/master-data-write', fn () => response('allowed'));
    }
}
