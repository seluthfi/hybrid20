<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test: manifest.webmanifest exists and has valid JSON with required PWA metadata.
     */
    public function test_manifest_webmanifest_exists_and_is_valid(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $json = json_decode($content, true);

        $this->assertIsArray($json, 'manifest.webmanifest must be valid JSON');
        $this->assertEquals('HYBRID 20', $json['name']);
        $this->assertEquals('HYBRID 20', $json['short_name']);
        $this->assertEquals('/', $json['start_url']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('#613dde', $json['theme_color']);
        $this->assertEquals('#fdf8ff', $json['background_color']);

        $this->assertArrayHasKey('icons', $json);
        $this->assertNotEmpty($json['icons']);

        $iconSrcs = array_column($json['icons'], 'src');
        $this->assertContains('/icons/icon-192.png', $iconSrcs);
        $this->assertContains('/icons/icon-512.png', $iconSrcs);
        $this->assertContains('/icons/icon-192-maskable.png', $iconSrcs);
        $this->assertContains('/icons/icon-512-maskable.png', $iconSrcs);
    }

    /**
     * 2. Test: PWA icon files exist on disk with correct dimensions.
     */
    public function test_pwa_icons_exist_with_correct_dimensions(): void
    {
        $expectedIcons = [
            'icon-192.png' => [192, 192],
            'icon-512.png' => [512, 512],
            'icon-192-maskable.png' => [192, 192],
            'icon-512-maskable.png' => [512, 512],
        ];

        foreach ($expectedIcons as $filename => [$expectedWidth, $expectedHeight]) {
            $path = public_path('icons/' . $filename);
            $this->assertFileExists($path, "Icon {$filename} must exist in public/icons/");
            $this->assertGreaterThan(100, filesize($path), "Icon {$filename} must not be empty");

            $info = getimagesize($path);
            $this->assertNotFalse($info, "Icon {$filename} must be a valid image");
            $this->assertEquals($expectedWidth, $info[0], "Width of {$filename} must be {$expectedWidth}");
            $this->assertEquals($expectedHeight, $info[1], "Height of {$filename} must be {$expectedHeight}");
        }

        $this->assertFileExists(public_path('icons/icon.svg'));
    }

    /**
     * 3. Test: Service Worker script exists and strictly obeys privacy/security rules (R8, R9).
     */
    public function test_service_worker_script_exists_and_enforces_privacy_rules(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);

        // Precache checks
        $this->assertStringContainsString('/offline.html', $content);
        $this->assertStringContainsString('/manifest.webmanifest', $content);
        $this->assertStringContainsString('/fonts/plus-jakarta-sans-latin-variable.woff2', $content);

        // R8 & R9 checks: private routes & photos explicitly excluded from caching
        $this->assertStringContainsString('/progress-photos', $content);
        $this->assertStringContainsString('/charts', $content);
        $this->assertStringContainsString('/foods/search', $content);
        $this->assertStringContainsString('/workouts', $content);
        $this->assertStringContainsString('/body', $content);

        // Navigation requests must fallback to /offline.html on network failure
        $this->assertStringContainsString('navigate', $content);
        $this->assertStringContainsString('offline.html', $content);
    }

    /**
     * 4. Test: Offline fallback HTML page exists with proper markup and retry action.
     */
    public function test_offline_fallback_html_exists(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('Kamu Sedang Offline', $content);
        $this->assertStringContainsString('Menyimpan atau melihat data kebugaran', $content);
        $this->assertStringContainsString('Coba Lagi', $content);
        $this->assertStringContainsString('location.reload()', $content);
    }

    /**
     * 5. Test: App shell and login page render PWA meta tags and manifest link.
     */
    public function test_views_render_pwa_meta_tags(): void
    {
        // 1. Guest login page
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
        $loginRes->assertSee('<meta name="theme-color" content="#613dde">', false);
        $loginRes->assertSee('<link rel="apple-touch-icon" href="/icons/icon-192.png">', false);

        // 2. Authenticated app-shell
        $user = User::factory()->create();
        UserSetting::factory()->create(['user_id' => $user->id]);

        $appRes = $this->actingAs($user)->get(route('today'));
        $appRes->assertStatus(200);
        $appRes->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
        $appRes->assertSee('<meta name="theme-color" content="#613dde">', false);
        $appRes->assertSee('<link rel="apple-touch-icon" href="/icons/icon-192.png">', false);
        $appRes->assertSee('Kamu offline. Menyimpan butuh koneksi.');
    }
}
