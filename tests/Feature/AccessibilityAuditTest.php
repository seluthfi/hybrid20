<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class AccessibilityAuditTest extends TestCase
{
    /**
     * Audit viewport configuration to ensure pinch-to-zoom is never disabled (PLAN.md R13).
     */
    public function test_viewport_does_not_block_user_zoom(): void
    {
        $layoutFiles = [
            resource_path('views/components/app-shell.blade.php'),
            resource_path('views/auth/login.blade.php'),
        ];

        foreach ($layoutFiles as $file) {
            $this->assertFileExists($file);
            $content = file_get_contents($file);

            $this->assertStringNotContainsString('user-scalable=no', $content, "Viewport in {$file} must not block zoom with user-scalable=no");
            $this->assertStringNotContainsString('maximum-scale=1', $content, "Viewport in {$file} must not restrict zoom with maximum-scale=1");
        }
    }

    /**
     * Audit status indicators to ensure status is always conveyed by icon + text (PLAN.md R13, §9.6).
     */
    public function test_status_badges_always_include_icon_and_text(): void
    {
        $badgeFile = resource_path('views/components/badge-status.blade.php');
        $this->assertFileExists($badgeFile);
        $content = file_get_contents($badgeFile);

        $this->assertStringContainsString('<x-icon', $content, 'Status badge must render an icon');
        $this->assertStringContainsString('$config[\'text\']', $content, 'Status badge must render text accompanied by icon');
    }

    /**
     * Audit reduced-motion support in CSS (PLAN.md R13, §9.5, §13 Fase 6B).
     */
    public function test_css_respects_prefers_reduced_motion(): void
    {
        $cssFile = resource_path('css/app.css');
        $this->assertFileExists($cssFile);
        $content = file_get_contents($cssFile);

        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $content);
        $this->assertStringContainsString('animation-duration: 0.01ms !important;', $content);
    }

    /**
     * Audit button components to ensure touch target is at least 44px (PLAN.md R13).
     */
    public function test_buttons_satisfy_minimum_touch_target_44px(): void
    {
        $buttonFile = resource_path('views/components/button.blade.php');
        $this->assertFileExists($buttonFile);
        $buttonContent = file_get_contents($buttonFile);

        // Verify min-h >= 44px
        $this->assertStringContainsString('min-h-[44px]', $buttonContent);

        $iconButtonFile = resource_path('views/components/icon-button.blade.php');
        $this->assertFileExists($iconButtonFile);
        $iconButtonContent = file_get_contents($iconButtonFile);

        // In Tailwind, w-11 h-11 = 44px x 44px
        $this->assertStringContainsString('w-11 h-11', $iconButtonContent);
    }

    /**
     * Audit text sizes to ensure no arbitrary text sizes below 12px exist (PLAN.md §9.2).
     */
    public function test_no_arbitrary_sub_12px_text_classes_in_views(): void
    {
        $viewsPath = resource_path('views');
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        $violations = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            // Search for text-[9px], text-[10px], text-[11px]
            if (preg_match('/text-\[(9|10|11)px\]/', $content, $matches)) {
                $violations[] = basename($file) . " uses sub-12px class '{$matches[0]}'";
            }
        }

        $this->assertEmpty($violations, "Audit ukuran teks < 12px (PLAN.md §9.2):\n" . implode("\n", $violations));
    }
}
