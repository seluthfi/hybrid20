<?php

namespace Tests\Feature;

use App\Http\View\Composers\AppShellComposer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AnimationBudgetAndCssAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_transition_all_in_blade_templates(): void
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $violations = [];

        foreach ($bladeFiles as $file) {
            $content = $file->getContents();
            if (str_contains($content, 'transition-all')) {
                $violations[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violations,
            'Forbidden "transition-all" found in blade templates: ' . implode(', ', $violations)
        );
    }

    public function test_no_transition_all_in_css(): void
    {
        $cssPath = resource_path('css/app.css');
        $this->assertFileExists($cssPath);

        $css = file_get_contents($cssPath);
        $this->assertFalse(
            str_contains($css, 'transition: all'),
            'Forbidden "transition: all" found in resources/css/app.css'
        );
        $this->assertFalse(
            str_contains($css, 'transition-all'),
            'Forbidden "transition-all" found in resources/css/app.css'
        );
    }

    public function test_no_fontsource_imports_in_app_css(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringNotContainsString('@import \'@fontsource', $css);
        $this->assertStringNotContainsString('@import "@fontsource', $css);
    }

    public function test_app_css_defines_self_hosted_fonts_with_block_display(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('font-family: \'Plus Jakarta Sans\'', $css);
        $this->assertStringContainsString('font-family: \'Space Grotesk\'', $css);
        $this->assertStringContainsString('font-display: block', $css);
        $this->assertStringContainsString('plus-jakarta-sans-latin-variable.woff2', $css);
        $this->assertStringContainsString('space-grotesk-latin-variable.woff2', $css);
        $this->assertStringContainsString('font-synthesis: none', $css);
        $this->assertStringContainsString('ascent-override', $css);
        $this->assertStringContainsString('descent-override', $css);
    }

    public function test_views_contain_font_preload_headers(): void
    {
        $appShell = file_get_contents(resource_path('views/components/app-shell.blade.php'));
        $login = file_get_contents(resource_path('views/auth/login.blade.php'));

        foreach ([$appShell, $login] as $view) {
            $this->assertStringContainsString('rel="preload"', $view);
            $this->assertStringContainsString('href="/fonts/plus-jakarta-sans-latin-variable.woff2"', $view);
            $this->assertStringContainsString('href="/fonts/space-grotesk-latin-variable.woff2"', $view);
        }
    }

    public function test_public_fonts_exist(): void
    {
        $this->assertFileExists(public_path('fonts/plus-jakarta-sans-latin-variable.woff2'));
        $this->assertFileExists(public_path('fonts/space-grotesk-latin-variable.woff2'));
    }

    public function test_app_css_defines_reduced_motion_and_fx_lite(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $css);
        $this->assertStringContainsString('.fx-lite', $css);
        $this->assertStringContainsString('@view-transition', $css);
        $this->assertStringContainsString('navigation: auto', $css);
        $this->assertStringContainsString('animate-badge-pop', $css);
        $this->assertStringContainsString('skeleton-shimmer', $css);
    }

    public function test_app_js_detects_low_power_device(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('fx-lite', $js);
        $this->assertStringContainsString('deviceMemory', $js);
        $this->assertStringContainsString('saveData', $js);
        $this->assertStringContainsString('prefers-reduced-motion', $js);
    }

    public function test_modal_overlays_do_not_use_backdrop_blur(): void
    {
        $viewsToCheck = [
            'resources/views/components/app-shell.blade.php',
            'resources/views/components/sheet.blade.php',
            'resources/views/components/confirm-dialog.blade.php',
            'resources/views/plan/index.blade.php',
            'resources/views/workouts/show.blade.php',
            'resources/views/workouts/create-gym.blade.php',
            'resources/views/workouts/edit-gym.blade.php',
            'resources/views/food_logs/index.blade.php',
            'resources/views/body/photos.blade.php',
            'resources/views/body/index.blade.php',
        ];

        foreach ($viewsToCheck as $relPath) {
            $fullPath = base_path($relPath);
            if (!file_exists($fullPath)) {
                continue;
            }

            $content = file_get_contents($fullPath);

            // Match full-screen fixed overlays
            preg_match_all('/fixed inset-0[^"\'>]*/', $content, $matches);
            foreach ($matches[0] as $match) {
                $this->assertStringNotContainsString(
                    'backdrop-blur',
                    $match,
                    "Forbidden backdrop-blur on full-screen overlay in {$relPath}: {$match}"
                );
            }
        }
    }

    public function test_app_shell_composer_registered_and_supplies_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertOk();

        $composer = app(AppShellComposer::class);
        $view = view('components.app-shell');
        $composer->compose($view);

        $this->assertArrayHasKey('shellToday', $view->getData());
        $this->assertArrayHasKey('shellLatestWeight', $view->getData());
        $this->assertArrayHasKey('shellTodaySteps', $view->getData());
    }
}
