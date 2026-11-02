<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class ForbiddenStringsAuditTest extends TestCase
{
    /**
     * Audit resources/ directory for forbidden words and phrases specified in PLAN.md §1, §11, and §14.
     */
    public function test_no_forbidden_strings_in_resources_directory(): void
    {
        $forbiddenTerms = [
            'streak',
            'lencana',
            'on-track',
            'kepatuhan',
            'defisit kalori',
            'Zona 2',
            'target pace',
            'estimasi kalori terbakar',
            'Fisioterapi',
            'Apple Health',
            'sinkronisasi cloud',
            'on-device',
            'terenkripsi',
            'Sub-50',
            'kondisi kebugaran',
        ];

        $viewsPath = resource_path('views');
        $jsPath = resource_path('js');

        $files = array_merge(
            $this->getFiles($viewsPath, ['blade.php']),
            $this->getFiles($jsPath, ['js'])
        );

        $violations = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            // Strip comments to focus on actual content/code
            $cleanContent = preg_replace('/\{\{--[\s\S]*?--\}\}/', '', $content);
            $cleanContent = preg_replace('/<!--[\s\S]*?-->/', '', $cleanContent);

            foreach ($forbiddenTerms as $term) {
                if (stripos($cleanContent, $term) !== false) {
                    $violations[] = basename($file) . " contains forbidden term: '{$term}'";
                }
            }

            // Check for 'Pekan' (must use 'Minggu' per PLAN.md R12)
            if (preg_match('/\bpekan\b/i', $cleanContent)) {
                $violations[] = basename($file) . " contains 'Pekan' (must use 'Minggu')";
            }

            // Check for formal 'Anda' (must use 'kamu' per PLAN.md R12)
            if (preg_match('/\bAnda\b/', $cleanContent)) {
                $violations[] = basename($file) . " contains 'Anda' (must use 'kamu')";
            }
        }

        $this->assertEmpty($violations, "Audit pelanggaran string terlarang (PLAN.md §11):\n" . implode("\n", $violations));
    }

    protected function getFiles(string $dir, array $extensions): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                foreach ($extensions as $ext) {
                    if (str_ends_with($file->getFilename(), $ext)) {
                        $files[] = $file->getPathname();
                        break;
                    }
                }
            }
        }

        return $files;
    }
}
