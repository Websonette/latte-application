<?php

declare(strict_types=1);

namespace Websonette\LatteApplication;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container;

/**
 * Minimal reusable foundation for an application's own bootstrap.
 *
 * Project-specific decisions such as debug mode, Tracy, configuration files,
 * timezone, and environment variables remain in the consuming application.
 */
final class Bootstrap
{
    private Configurator $configurator;

    public function __construct(string $rootDirectory)
    {
        $rootDirectory = rtrim($rootDirectory, '/\\');

        $this->configurator = new Configurator();
        $this->configurator->addStaticParameters(['rootDir' => $rootDirectory]);
        $this->configurator->setTempDirectory($rootDirectory . '/temp');
    }

    public function configurator(): Configurator
    {
        return $this->configurator;
    }

    public function boot(): Container
    {
        return $this->configurator->createContainer();
    }
}
