<?php

namespace Tests\Feature;

use Tests\TestCase;

class BuildAssetTest extends TestCase
{
    public function test_compiled_css_does_not_contain_reference_html_invalid_classes(): void
    {
        $cssFiles = glob(public_path('build/assets/*.css'));

        $this->assertNotEmpty($cssFiles, 'File CSS hasil kompilasi Vite belum ditemukan di public/build/assets/. Jalankan npm run build terlebih dahulu.');

        $combinedCss = '';
        foreach ($cssFiles as $file) {
            $combinedCss .= file_get_contents($file);
        }

        // Pastikan kelas khas referensi HTML yang tidak valid tidak bocor ke build
        $this->assertStringNotContainsString('w-13', $combinedCss, 'Kelas w-13 dari referensi tidak boleh masuk ke hasil build.');
        $this->assertStringNotContainsString('h-5.5', $combinedCss, 'Kelas h-5.5 dari referensi tidak boleh masuk ke hasil build.');
        $this->assertStringNotContainsString('p- space-md', $combinedCss, 'Typo p- space-md tidak boleh masuk ke hasil build.');
    }

    public function test_manifest_and_assets_do_not_contain_external_cdn_references(): void
    {
        $manifestPath = public_path('build/manifest.json');

        if (file_exists($manifestPath)) {
            $content = file_get_contents($manifestPath);
            $this->assertStringNotContainsString('fonts.bunny.net', $content);
            $this->assertStringNotContainsString('fonts.googleapis.com', $content);
        }

        $cssFiles = glob(public_path('build/assets/*.css'));
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('fonts.bunny.net', $content);
            $this->assertStringNotContainsString('fonts.googleapis.com', $content);
        }
    }
}
