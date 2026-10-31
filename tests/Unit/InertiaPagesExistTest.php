<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Every Inertia::render('X') / inertia('X') in the PHP code must have a matching
 * resources/js/Pages/X.vue — with the exact case, so that it also works on Linux.
 */
class InertiaPagesExistTest extends TestCase
{
    private function basePath(string $path = ''): string
    {
        return dirname(__DIR__, 2).($path ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * @return array<string, string> page name => "file:line" where it is rendered
     */
    private function renderedPages(): array
    {
        $pages = [];

        foreach (['app', 'routes'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->basePath($dir)));

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                foreach (file($file->getPathname()) as $index => $line) {
                    if (preg_match_all("/(?:Inertia::render|inertia)\(\s*['\"]([^'\"]+)['\"]/", $line, $matches)) {
                        foreach ($matches[1] as $page) {
                            $pages[$page] = $file->getFilename().':'.($index + 1);
                        }
                    }
                }
            }
        }

        return $pages;
    }

    /**
     * @return list<string> existing page names, e.g. "Doctor/Slots"
     */
    private function existingPages(): array
    {
        $root = $this->basePath('resources/js/Pages');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        $pages = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'vue') {
                $relative = substr($file->getPathname(), strlen($root) + 1, -strlen('.vue'));
                $pages[] = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
            }
        }

        return $pages;
    }

    public function test_the_scan_finds_the_rendered_pages(): void
    {
        // Garde-fou : si la regex ne trouvait plus rien, le test suivant passerait à vide
        $this->assertArrayHasKey('Dashboards/PatientDashboard', $this->renderedPages());
    }

    public function test_every_rendered_inertia_page_exists(): void
    {
        $existing = $this->existingPages();

        $missing = array_filter(
            $this->renderedPages(),
            fn (string $where, string $page) => ! in_array($page, $existing, true),
            ARRAY_FILTER_USE_BOTH,
        );

        $this->assertSame(
            [],
            $missing,
            "Inertia pages rendered but missing in resources/js/Pages (page => where):\n".print_r($missing, true),
        );
    }
}
