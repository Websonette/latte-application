<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Websonette\LatteApplication\UI\FlashMessage;

final class FlashMessageTypeTest extends TestCase
{
    #[TestWith([FlashMessage::Error, 'error'])]
    #[TestWith([FlashMessage::Info, 'info'])]
    #[TestWith([FlashMessage::Success, 'success'])]
    #[TestWith([FlashMessage::Warning, 'warning'])]
    public function testHasStableTemplateValue(FlashMessage $type, string $value): void
    {
        self::assertSame($value, $type->value);
    }

    #[TestWith([FlashMessage::Error, 'websonette.latte-application.flash.error'])]
    #[TestWith([FlashMessage::Info, 'websonette.latte-application.flash.info'])]
    #[TestWith([FlashMessage::Success, 'websonette.latte-application.flash.success'])]
    #[TestWith([FlashMessage::Warning, 'websonette.latte-application.flash.warning'])]
    public function testDefinesQualifiedTranslationKey(FlashMessage $type, string $key): void
    {
        self::assertSame($key, $type->translationKey());
    }
}
