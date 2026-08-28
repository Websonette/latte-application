<?php

declare(strict_types=1);

namespace App;

use Nette\DI\Container;
use Symfony\Component\Dotenv\Dotenv;
use Websonette\LatteApplication\Bootstrap as PackageBootstrap;

final class Bootstrap
{
    public static function boot(): Container
    {
        $rootDirectory = dirname(__DIR__);
        self::loadEnvironment(dirname($rootDirectory, 2) . '/.env');

        $bootstrap = new PackageBootstrap($rootDirectory);
        $configurator = $bootstrap->configurator();

        $configurator->setDebugMode(self::environmentBool('APP_DEBUG', true));
        $configurator->enableTracy($rootDirectory . '/log');
        $configurator->setTimeZone(self::environment('APP_TIMEZONE', 'Europe/Prague'));
        $configurator->addDynamicParameters([
            'env' => array_merge(getenv(), $_SERVER, $_ENV),
        ]);
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();
        $configurator->addConfig($rootDirectory . '/config/common.neon');

        return $bootstrap->boot();
    }

    private static function loadEnvironment(string $environmentFile): void
    {
        if (is_file($environmentFile)) {
            (new Dotenv())->loadEnv($environmentFile);
        }
    }

    private static function environment(string $name, string $default): string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private static function environmentBool(string $name, bool $default): bool
    {
        $value = self::environment($name, $default ? '1' : '0');
        $parsed = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }
}
