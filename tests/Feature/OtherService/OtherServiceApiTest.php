<?php

namespace Tests\Feature\OtherService;

use App\Modules\OtherService\Models\OtherService;
use App\Modules\User\Enums\UserRole;
use App\Modules\User\Models\User;
use Database\Seeders\OtherServiceSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OtherServiceApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('other_services');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_full_name');
            $table->string('user_email')->unique();
            $table->string('user_phone')->nullable();
            $table->timestamp('user_email_verified_at')->nullable();
            $table->string('user_password');
            $table->string('user_role')->default('agent');
            $table->boolean('user_is_active')->default(true);
            $table->timestamp('user_last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        $migration = require database_path('migrations/2026_09_19_200000_create_other_services_table.php');
        $migration->up();
    }

    public function test_seeder_is_idempotent_and_creates_twelve_services(): void
    {
        $this->seed(OtherServiceSeeder::class);
        $this->seed(OtherServiceSeeder::class);

        $this->assertSame(12, OtherService::query()->count());
        $this->assertTrue(
            OtherService::query()->where('other_service_slug', 'documentation')->exists(),
        );
    }

    public function test_public_api_returns_only_active_services_ordered_by_sort_order(): void
    {
        $this->seed(OtherServiceSeeder::class);

        OtherService::query()
            ->where('other_service_slug', 'fmb')
            ->update(['other_service_is_active' => false]);

        $response = $this->getJson('/api/other-services');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonMissing(['other_service_slug' => 'fmb']);

        $data = $response->json('data');
        $this->assertCount(11, $data);
        $this->assertSame('Documentation', $data[0]['other_service_name']);
        $this->assertSame(1, $data[0]['other_service_sort_order']);
        $this->assertArrayNotHasKey('other_service_is_active', $data[0]);
        $this->assertArrayNotHasKey('other_service_created_at', $data[0]);
    }

    public function test_public_prefix_alias_matches_root_public_endpoint(): void
    {
        $this->seed(OtherServiceSeeder::class);

        $root = $this->getJson('/api/other-services')->json('data');
        $prefixed = $this->getJson('/api/public/other-services')->json('data');

        $this->assertSame($root, $prefixed);
    }

    public function test_admin_can_create_list_update_status_and_deactivate(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $create = $this->postJson('/api/admin/other-services', [
            'other_service_name' => 'Survey Sketch',
            'other_service_description' => 'Custom survey sketch service',
            'other_service_icon' => 'survey',
            'other_service_sort_order' => 20,
        ]);

        $create->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.other_service_name', 'Survey Sketch')
            ->assertJsonPath('data.other_service_slug', 'survey-sketch')
            ->assertJsonPath('data.other_service_is_active', true);

        $id = (int) $create->json('data.other_service_id');

        $list = $this->getJson('/api/admin/other-services?search=Survey');
        $list->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('pagination.total', 1);

        $update = $this->putJson("/api/admin/other-services/{$id}", [
            'other_service_name' => 'Survey Sketch Updated',
            'other_service_sort_order' => 25,
        ]);

        $update->assertOk()
            ->assertJsonPath('data.other_service_name', 'Survey Sketch Updated')
            ->assertJsonPath('data.other_service_slug', 'survey-sketch')
            ->assertJsonPath('data.other_service_sort_order', 25);

        $deactivate = $this->patchJson("/api/admin/other-services/{$id}/status", [
            'other_service_is_active' => false,
        ]);

        $deactivate->assertOk()
            ->assertJsonPath('data.other_service_is_active', false);

        $this->getJson('/api/other-services')
            ->assertOk()
            ->assertJsonMissing(['other_service_slug' => 'survey-sketch']);

        $delete = $this->deleteJson("/api/admin/other-services/{$id}");
        $delete->assertOk()
            ->assertJsonPath('data.other_service_is_active', false);

        $this->assertDatabaseHas('other_services', [
            'other_service_id' => $id,
            'other_service_is_active' => 0,
        ]);
    }

    public function test_admin_endpoints_require_authentication(): void
    {
        $this->getJson('/api/admin/other-services')->assertUnauthorized();
        $this->postJson('/api/admin/other-services', [
            'other_service_name' => 'Unauthorized Service',
        ])->assertUnauthorized();
    }

    public function test_duplicate_name_is_rejected(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $this->postJson('/api/admin/other-services', [
            'other_service_name' => 'Documentation',
            'other_service_slug' => 'documentation',
        ])->assertCreated();

        $this->postJson('/api/admin/other-services', [
            'other_service_name' => 'Documentation',
            'other_service_slug' => 'documentation-2',
        ])->assertStatus(422);
    }

    public function test_status_accepts_is_active_alias(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $service = OtherService::query()->create([
            'other_service_name' => 'Alias Status',
            'other_service_slug' => 'alias-status',
            'other_service_sort_order' => 1,
            'other_service_is_active' => true,
        ]);

        $this->patchJson("/api/admin/other-services/{$service->other_service_id}/status", [
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.other_service_is_active', false);
    }

    private function makeAdmin(): User
    {
        return User::query()->create([
            'user_full_name' => 'Admin Tester',
            'user_email' => 'admin-other-service@example.com',
            'user_password' => 'Password@123',
            'user_role' => UserRole::Admin,
            'user_is_active' => true,
        ]);
    }
}
