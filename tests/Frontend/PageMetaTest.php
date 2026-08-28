<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests\Frontend;

use PHPUnit\Framework\TestCase;
use Websonette\LatteApplication\Frontend\PageMeta;

final class PageMetaTest extends TestCase
{
    public function testNormalizesAndSerializesValues(): void
    {
        $meta = new PageMeta(' public ', ' Homepage ', ' default ');

        self::assertSame(
            [
                'module' => 'public',
                'presenter' => 'Homepage',
                'action' => 'default',
            ],
            $meta->toArray(),
        );
        self::assertSame($meta->toArray(), $meta->jsonSerialize());
    }

    public function testOmitsMissingAndEmptyValues(): void
    {
        $meta = new PageMeta(' ', null, 'default');

        self::assertSame(['action' => 'default'], $meta->toArray());
    }
}