<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests;

use PHPUnit\Framework\TestCase;
use Websonette\LatteApplication\DI\LatteApplicationExtension;

final class TranslationProviderTest extends TestCase
{
    public function testProvidesContributteTranslationResources(): void
    {
        $resources = (new LatteApplicationExtension())->getTranslationResources();

        self::assertCount(1, $resources);
        self::assertDirectoryExists($resources[0]);
        self::assertFileExists($resources[0] . '/websonette.en.neon');
        self::assertFileExists($resources[0] . '/websonette.cs.neon');
    }
}