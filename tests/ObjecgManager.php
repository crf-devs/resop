<?php

declare(strict_types=1);

// This file is used by phpstan

use App\Kernel;

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();

return $container->get('doctrine')->getManager();
