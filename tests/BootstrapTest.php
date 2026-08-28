<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests;

use Nette\Bootstrap\Configurator;
use PHPUnit\Framework\TestCase;
use Websonette\LatteApplication\Bootstrap;

final class BootstrapTest extends TestCase
{
    public function testExposesConfiguratorForApplicationSpecificSetup(): void
    {
        $rootDirectory = sys_get_temp_dir() . '/websonette-latte-application-test';
        @mkdir($rootDirectory . '/temp', recursive: true);

        $bootstrap = new Bootstrap($rootDirectory);

        self::assertInstanceOf(Configurator::class, $bootstrap->configurator());
    }
}
