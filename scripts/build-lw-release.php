<?php
/** Build the checksummed LuxWrap Studio application manifest. */

$root = dirname(__DIR__);
$manifestPath = $root . '/lw-release.json';
$routes = [
    'step/recetario-keto/' => 'step/recetario-keto/index.php',
    'step/recetario-keto/checkout.php' => 'step/recetario-keto/checkout.php',
    'step/recetario-keto/success.php' => 'step/recetario-keto/success.php',
    'step/compra-nochebuena-keto/' => 'step/compra-nochebuena-keto/index.php',
    'step/compra-nochebuena-keto/checkout.php' => 'step/compra-nochebuena-keto/checkout.php',
    'step/compra-nochebuena-keto/success.php' => 'step/compra-nochebuena-keto/success.php',
    'step/postres-y-snacks-keto/' => 'step/postres-y-snacks-keto/index.php',
    'step/postres-y-snacks-keto/checkout.php' => 'step/postres-y-snacks-keto/checkout.php',
    'step/postres-y-snacks-keto/success.php' => 'step/postres-y-snacks-keto/success.php',
    'step/recetario-keto-thank-you/' => 'step/recetario-keto-thank-you/index.php',
    'step/gracias-por-tu-compra-postres/' => 'step/gracias-por-tu-compra-postres/index.php',
];

$files = [];
$required = array_values(array_unique(array_merge(array_values($routes), [
    'scripts/env-loader.php',
    'scripts/products.php',
    'scripts/process-payment.php',
    'scripts/render-checkout.php',
    'scripts/render-success.php',
    'scripts/render-thankyou.php',
])));

$assetIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/assets', FilesystemIterator::SKIP_DOTS)
);
foreach ($assetIterator as $asset) {
    if ($asset->isFile()) {
        $required[] = str_replace('\\', '/', substr($asset->getPathname(), strlen($root) + 1));
    }
}

sort($required, SORT_STRING);
foreach (array_unique($required) as $relative) {
    $absolute = $root . '/' . $relative;
    if (!is_file($absolute)) {
        fwrite(STDERR, "Missing release file: {$relative}\n");
        exit(1);
    }
    $files[$relative] = hash_file('sha256', $absolute);
}

$manifest = [
    'contract_version' => 2,
    'runtime' => 'php',
    'site' => 'pay-opin-x',
    'version' => '1.0.1',
    'languages' => ['es'],
    'entries' => ['es' => 'step/recetario-keto/index.php'],
    'routes' => $routes,
    'redirects' => ['' => 'step/recetario-keto/'],
    'files_sha256' => $files,
];

file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
echo "Built lw-release.json for {$manifest['site']} {$manifest['version']}\n";
