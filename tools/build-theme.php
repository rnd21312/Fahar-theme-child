<?php
/**
 * Build an installable Fahar Theme Child zip.
 * Run: php tools/build-theme.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$version = '1.0.0';
$style = file_get_contents($root . '/style.css');
if (preg_match('/Version:\s*([^\r\n]+)/', $style, $match)) {
    $version = trim($match[1]);
}

$build = $root . '/build';
if (!is_dir($build)) {
    mkdir($build, 0777, true);
}

$zipPath = $build . '/fahar-theme-child-' . $version . '.zip';
if (file_exists($zipPath)) {
    unlink($zipPath);
}

$zip = new PharData($zipPath);

$exclude = [
    '/.git/',
    '/.github/',
    '/.agents/',
    '/node_modules/',
    '/build/',
    '/tests/',
    '/tools/',
    '.DS_Store',
    'Thumbs.db',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $relative = ltrim(str_replace($root, '', $path), '/');

    foreach ($exclude as $item) {
        if (str_contains('/' . $relative, $item) || $relative === $item) {
            continue 2;
        }
    }

    $zip->addFile($path, 'fahar-theme-child/' . $relative);
}

echo "Built: {$zipPath}" . PHP_EOL;
