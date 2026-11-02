<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiKedipDanScrollTest extends TestCase
{
    /**
     * Memastikan resources/css/app.css memuat aturan [x-cloak] dengan display: none !important.
     */
    public function test_app_css_contains_x_cloak_rule(): void
    {
        $cssPath = resource_path('css/app.css');
        $this->assertFileExists($cssPath);

        $content = file_get_contents($cssPath);
        $this->assertMatchesRegularExpression(
            '/\[x-cloak\]\s*\{\s*display:\s*none\s*!important;?\s*\}/i',
            $content,
            'app.css harus memuat aturan [x-cloak] { display: none !important; }'
        );
    }

    /**
     * Memastikan app-shell.blade.php dan login.blade.php memiliki inline style [x-cloak] di <head>.
     */
    public function test_app_shell_and_login_have_inline_x_cloak_style(): void
    {
        $shellPath = resource_path('views/components/app-shell.blade.php');
        $this->assertFileExists($shellPath);
        $shellContent = file_get_contents($shellPath);
        $this->assertStringContainsString('<style>[x-cloak]{display:none!important}</style>', $shellContent);

        $loginPath = resource_path('views/auth/login.blade.php');
        $this->assertFileExists($loginPath);
        $loginContent = file_get_contents($loginPath);
        $this->assertStringContainsString('<style>[x-cloak]{display:none!important}</style>', $loginContent);
    }

    /**
     * Memastikan app-shell memiliki @pagehide.window handler pada <body> untuk mereset modal saat navigasi.
     */
    public function test_app_shell_body_has_pagehide_handler(): void
    {
        $shellPath = resource_path('views/components/app-shell.blade.php');
        $content = file_get_contents($shellPath);

        $this->assertStringContainsString('@pagehide.window', $content);
        $this->assertStringContainsString('freeWorkoutModalOpen = false', $content);
        $this->assertStringContainsString('quickActionOpen = false', $content);
        $this->assertStringContainsString('weightModalOpen = false', $content);
        $this->assertStringContainsString('stepsModalOpen = false', $content);
    }

    /**
     * Memastikan seluruh elemen yang ber-x-show di seluruh resources/views memiliki x-cloak
     * (atau berada di dalam kontainer yang ber-x-cloak / memakai style="display:none;").
     */
    public function test_all_x_show_elements_in_blade_files_have_x_cloak(): void
    {
        $viewsPath = resource_path('views');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsPath));

        $missingCloak = [];

        foreach ($files as $file) {
            if ($file->isDir() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $relPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Cari semua tag yang memuat atribut x-show
            // Pola: <tagname ... x-show="..." ... >
            if (preg_match_all('/<([a-zA-Z0-9\-:]+)([^>]*?x-show=(?:"[^"]*"|\'[^\']*\')[^>]*)>/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $tagStr = $match[0];
                    $hasCloak = str_contains($tagStr, 'x-cloak');
                    $hasDisplayNone = str_contains($tagStr, 'display: none') || str_contains($tagStr, 'display:none');

                    if (! $hasCloak && ! $hasDisplayNone) {
                        // Jika tidak ada di tag langsung, periksa apakah tag ini berada di dalam parent yang ber-x-cloak
                        // (misal template atau sub-elemen modal)
                        $offset = $match[1];
                        $preceding = substr($content, 0, $offset);
                        $hasAncestorCloak = str_contains($preceding, 'x-cloak');

                        if (! $hasAncestorCloak) {
                            $missingCloak[] = "{$relPath}: " . trim(preg_replace('/\s+/', ' ', $tagStr));
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $missingCloak,
            'Ditemukan elemen x-show tanpa x-cloak: ' . implode("\n", $missingCloak)
        );
    }

    /**
     * Memastikan resources/js/app.js memuat logika scroll restoration otomatis.
     */
    public function test_app_js_contains_scroll_preservation_logic(): void
    {
        $jsPath = resource_path('js/app.js');
        $this->assertFileExists($jsPath);

        $content = file_get_contents($jsPath);

        $this->assertStringContainsString('hybrid20:scroll', $content);
        $this->assertStringContainsString('sessionStorage', $content);
        $this->assertStringContainsString('data-scroll-keep', $content);
        $this->assertStringContainsString('data-no-preserve-scroll', $content);
        $this->assertStringContainsString('requestAnimationFrame', $content);
        $this->assertStringContainsString('window.scrollTo', $content);
    }

    /**
     * Memastikan pemilih minggu dan komponen horizontal strip memiliki atribut data-scroll-keep.
     */
    public function test_plan_and_week_strip_have_scroll_keep_attributes(): void
    {
        $planPath = resource_path('views/plan/index.blade.php');
        $planContent = file_get_contents($planPath);
        $this->assertStringContainsString('id="plan-week-selector"', $planContent);
        $this->assertStringContainsString('data-scroll-keep', $planContent);

        $stripPath = resource_path('views/components/week-strip.blade.php');
        $stripContent = file_get_contents($stripPath);
        $this->assertStringContainsString('data-scroll-keep', $stripContent);
    }
}
