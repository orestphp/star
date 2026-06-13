<?php
// [api/tests/bootstrap.php]

require __DIR__ . '/../vendor/autoload.php';

// Initialize Nette Tester assertions
Tester\Environment::setup();

// Initialize the classic Configurator
$configurator = new Nette\Bootstrap\Configurator;

// Use the test cache directory we unlocked with chmod 777
$configurator->setTempDirectory(__DIR__ . '/../temp/tests');

// Load your actual configuration files
$configurator->addConfig(__DIR__ . '/../config/common.neon');
$configurator->addConfig(__DIR__ . '/../config/services.neon');

return $configurator->createContainer();