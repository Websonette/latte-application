<?php

declare(strict_types=1);

use App\Bootstrap;
use Nette\Application\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = Bootstrap::boot();
$container->getByType(Application::class)->run();
