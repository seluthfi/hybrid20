<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppShellAndLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_locale_is_configured_to_id(): void
    {
        $this->assertEquals('id', config('app.locale'));
        $this->assertEquals('id', config('app.fallback_locale'));
        $this->assertEquals('id_ID', config('app.faker_locale'));
        $this->assertEquals('id', Carbon::getLocale());
    }

    public function test_authenticated_user_can_access_today_view_with_app_shell(): void
    {
        $user = User::factory()->create(['name' => 'Atlet Tangguh']);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Atlet Tangguh');
        $response->assertSee('Hari Ini');
        $response->assertSee('Latihan');
        $response->assertSee('Makan');
        $response->assertSee('Tubuh');
        $response->assertSee('HYBRID 20');
    }

    public function test_dev_components_accessible_in_local_environment(): void
    {
        $response = $this->get('/_dev/components');

        $response->assertStatus(200);
        $response->assertSee('Komponen Liquid Glass Athletic');
    }
}
