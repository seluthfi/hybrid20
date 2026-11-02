<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class BladeIntegrityAndIconsTest extends TestCase
{
    /**
     * Get all blade files from resources/views.
     */
    protected function getBladeFiles(): array
    {
        $dir = new RecursiveDirectoryIterator(resource_path('views'));
        $iter = new RecursiveIteratorIterator($dir);
        $files = [];

        foreach ($iter as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Test: No nested <button> or <a> inside <button> or <a>.
     */
    public function test_no_nested_interactive_elements_in_blade_views(): void
    {
        $files = $this->getBladeFiles();
        $violations = [];

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            $tokens = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $stack = [];

            foreach ($tokens as $token) {
                if (!str_starts_with($token, '<') || str_starts_with($token, '<?') || str_starts_with($token, '<!')) {
                    continue;
                }

                if (preg_match('/^<(\/)?([a-zA-Z0-9\-]+)/', $token, $m)) {
                    $isClose = ($m[1] === '/');
                    $tag = strtolower($m[2]);
                    $isInteractive = in_array($tag, ['button', 'a', 'x-button']);
                    $isSelfClosing = str_ends_with(rtrim($token), '/>');

                    if ($isClose) {
                        for ($k = count($stack) - 1; $k >= 0; $k--) {
                            if ($stack[$k] === $tag) {
                                array_splice($stack, $k, 1);
                                break;
                            }
                        }
                    } elseif ($isInteractive) {
                        if (!empty($stack)) {
                            $parent = end($stack);
                            $violations[] = basename($filePath) . " has <{$tag}> nested inside <{$parent}>: " . trim(preg_replace('/\s+/', ' ', substr($token, 0, 100)));
                        }
                        if (!$isSelfClosing) {
                            $stack[] = $tag;
                        }
                    }
                }
            }
        }

        $this->assertEmpty($violations, "Found nested interactive elements:\n" . implode("\n", $violations));
    }

    /**
     * Test: All <x-icon name="..."> used in Blade are defined in components/icon.blade.php.
     */
    public function test_all_icon_names_in_blade_views_are_defined(): void
    {
        $iconBlade = file_get_contents(resource_path('views/components/icon.blade.php'));
        $definedIcons = [];
        if (preg_match('/\$icons\s*=\s*\[(.*?)\];/s', $iconBlade, $matches)) {
            if (preg_match_all('/\'([a-z0-9_\-]+)\'\s*=>/i', $matches[1], $iconMatches)) {
                $definedIcons = array_fill_keys($iconMatches[1], true);
            }
        }

        $this->assertNotEmpty($definedIcons, 'Defined icons list should not be empty');

        $files = $this->getBladeFiles();
        $undefinedIcons = [];

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            // Match static name attributes: <x-icon name="..."
            if (preg_match_all('/<x-icon\s+[^>]*name=[\'"]([a-zA-Z0-9_\-]+)[\'"]/i', $content, $matches)) {
                foreach ($matches[1] as $name) {
                    if (!isset($definedIcons[$name])) {
                        $undefinedIcons[] = basename($filePath) . " uses undefined icon '{$name}'";
                    }
                }
            }

            // Match ternary expressions like: :name="$isGym ? 'fitness_center' : 'directions_run'"
            if (preg_match_all('/<x-icon\s+[^>]*:name=[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
                foreach ($matches[1] as $expr) {
                    if (preg_match_all('/\'([a-zA-Z0-9_\-]+)\'/', $expr, $nameMatches)) {
                        foreach ($nameMatches[1] as $name) {
                            if (!isset($definedIcons[$name])) {
                                $undefinedIcons[] = basename($filePath) . " uses dynamic undefined icon '{$name}' in [{$expr}]";
                            }
                        }
                    }
                }
            }
        }

        $this->assertEmpty($undefinedIcons, "Found undefined icons in views:\n" . implode("\n", $undefinedIcons));
    }

    /**
     * Test: Every tag with x-show has x-cloak attribute.
     */
    public function test_all_x_show_elements_have_x_cloak(): void
    {
        $files = $this->getBladeFiles();
        $missingCloak = [];

        foreach ($files as $filePath) {
            $c = file_get_contents($filePath);
            $len = strlen($c);
            $i = 0;
            while ($i < $len) {
                if ($c[$i] === '<' && $i + 1 < $len && (ctype_alpha($c[$i+1]) || $c[$i+1] === 'x')) {
                    $start = $i;
                    $inQuote = false;
                    $quoteChar = '';
                    $tagStr = '';
                    while ($i < $len) {
                        $char = $c[$i];
                        if (!$inQuote && ($char === '"' || $char === "'")) {
                            $inQuote = true;
                            $quoteChar = $char;
                        } elseif ($inQuote && $char === $quoteChar) {
                            $inQuote = false;
                        } elseif (!$inQuote && $char === '>') {
                            $tagStr = substr($c, $start, $i - $start + 1);
                            break;
                        }
                        $i++;
                    }

                    if (str_contains($tagStr, 'x-show') && !str_contains($tagStr, 'x-cloak')) {
                        $lineNum = substr_count(substr($c, 0, $start), "\n") + 1;
                        $missingCloak[] = basename($filePath) . ":{$lineNum} -> " . trim(preg_replace('/\s+/', ' ', $tagStr));
                    }
                }
                $i++;
            }
        }

        $this->assertEmpty($missingCloak, "Found elements with x-show missing x-cloak:\n" . implode("\n", $missingCloak));
    }

    /**
     * Test: No <input type="number"> in application forms.
     */
    public function test_no_type_number_inputs_in_views(): void
    {
        $files = $this->getBladeFiles();
        $violations = [];

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            if (preg_match_all('/<input[^>]*type=[\'"]number[\'"]/i', $content, $matches)) {
                $violations[] = basename($filePath) . " contains <input type=\"number\">";
            }
        }

        $this->assertEmpty($violations, "Found <input type=\"number\"> in views:\n" . implode("\n", $violations));
    }

    /**
     * Test: All <button type="button"> have an action handler or are intentionally disabled/component wrappers.
     */
    public function test_all_button_type_button_have_handlers(): void
    {
        $files = $this->getBladeFiles();
        $violations = [];

        foreach ($files as $filePath) {
            $baseName = basename($filePath);
            if (in_array($baseName, ['button.blade.php', 'icon-button.blade.php'])) {
                continue;
            }

            $c = file_get_contents($filePath);
            $len = strlen($c);
            $i = 0;
            while ($i < $len) {
                if ($c[$i] === '<' && substr($c, $i, 7) === '<button') {
                    $start = $i;
                    $inQuote = false;
                    $quoteChar = '';
                    $tagStr = '';
                    while ($i < $len) {
                        $char = $c[$i];
                        if (!$inQuote && ($char === '"' || $char === "'")) {
                            $inQuote = true;
                            $quoteChar = $char;
                        } elseif ($inQuote && $char === $quoteChar) {
                            $inQuote = false;
                        } elseif (!$inQuote && $char === '>') {
                            $tagStr = substr($c, $start, $i - $start + 1);
                            break;
                        }
                        $i++;
                    }

                    if (preg_match('/type=[\'"]button[\'"]/i', $tagStr)) {
                        $hasHandler = preg_match('/(@click|x-on:click|onclick|disabled|\$attributes)/i', $tagStr);
                        if (!$hasHandler) {
                            $lineNum = substr_count(substr($c, 0, $start), "\n") + 1;
                            $violations[] = "{$baseName}:{$lineNum} button type=\"button\" has no handler: " . trim(preg_replace('/\s+/', ' ', $tagStr));
                        }
                    }
                }
                $i++;
            }
        }

        $this->assertEmpty($violations, "Found <button type=\"button\"> without handlers:\n" . implode("\n", $violations));
    }
}
