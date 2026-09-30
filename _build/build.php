<?php
declare(strict_types=1);

$projectRoot = realpath(__DIR__ . '/..');
if ($projectRoot === false) {
    fwrite(STDERR, "ERROR: project root not found\n");
    exit(1);
}

$modxRoot = '/Users/dos/Desktop/Plugins/modx';
$corePath = rtrim($modxRoot, "/\\") . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
$configFile = $corePath . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php';

if (!is_file($configFile)) {
    fwrite(STDERR, "ERROR: MODX config not found: {$configFile}\n");
    exit(1);
}

define('MODX_API_MODE', true);
define('MODX_CORE_PATH', $corePath);
define('MODX_CONFIG_KEY', 'config');

require_once $configFile;
require_once $corePath . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!class_exists(\MODX\Revolution\modX::class)) {
    require_once $corePath . 'src/Revolution/modX.php';
}
if (!class_exists(\MODX\Revolution\Transport\modPackageBuilder::class)) {
    require_once $corePath . 'src/Revolution/Transport/modPackageBuilder.php';
}

$modx = new \MODX\Revolution\modX();
if (!$modx->initialize('mgr')) {
    fwrite(STDERR, "ERROR: MODX initialization failed\n");
    exit(1);
}

$builder = new \MODX\Revolution\Transport\modPackageBuilder($modx);
$builder->setWorkspace(1);
$builder->createPackage('UniversalEpay', '1.0.0', 'beta2');

$builder->setPackageAttributes([
    'author' => 'UniversalEpay',
    'license' => 'MIT',
    'readme' => 'UniversalEpay — Halyk ePay for MODX 3 and MiniShop3.',
    'changelog' => 'Halyk credentials in CMP, MiniShop3 payment provider, Halyk callback/status verification.',
]);

$coreSource = $projectRoot . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'universalepay';
$assetsSource = $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'universalepay';
$resolverSource = $projectRoot . DIRECTORY_SEPARATOR . '_build' . DIRECTORY_SEPARATOR . 'resolvers' . DIRECTORY_SEPARATOR . 'install.resolver.php';

$builder->registerNamespace(
    'universalepay',
    false,
    false,
    '{core_path}components/universalepay/',
    '{assets_path}components/universalepay/'
);

$vehicle = $builder->createVehicle($builder->namespace, [
    \xPDO\Transport\xPDOTransport::UNIQUE_KEY => 'name',
    \xPDO\Transport\xPDOTransport::PRESERVE_KEYS => true,
    \xPDO\Transport\xPDOTransport::UPDATE_OBJECT => true,
    \xPDO\Transport\xPDOTransport::RESOLVE_FILES => true,
    \xPDO\Transport\xPDOTransport::RESOLVE_PHP => true,
]);

$vehicle->resolve('file', [
    'source' => $coreSource,
    'target' => "return MODX_CORE_PATH . 'components/';",
]);

if (is_dir($assetsSource)) {
    $vehicle->resolve('file', [
        'source' => $assetsSource,
        'target' => "return MODX_ASSETS_PATH . 'components/';",
    ]);
}

$vehicle->resolve('php', [
    'source' => $resolverSource,
]);

if (!$builder->putVehicle($vehicle)) {
    fwrite(STDERR, "ERROR: Could not put vehicle into package\n");
    exit(1);
}

if (!$builder->pack()) {
    fwrite(STDERR, "ERROR: Package packing failed\n");
    exit(1);
}

echo "SUCCESS: UniversalEpay package created\n";
echo "Signature: " . $builder->getSignature() . "\n";
echo "Package: " . $builder->directory . $builder->getSignature() . ".transport.zip\n";
