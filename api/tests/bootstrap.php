<?php
// [api/tests/bootstrap.php]

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Bootstrap\Configurator;
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp/tests');

// Pass an empty configuration array instead
$configurator->addConfig([
    'parameters' => [],
    'services' => []
]);

return $configurator->createContainer();