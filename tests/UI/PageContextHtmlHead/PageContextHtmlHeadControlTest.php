<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests\UI\PageContextHtmlHead;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use PHPUnit\Framework\TestCase;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControl;
use Websonette\WebApplication\Page\PageContext;

final class PageContextHtmlHeadControlTest extends TestCase
{
    public function testRendersSeoHeadWithoutInvalidFlowWrapper(): void
    {
        $page = (new PageContext())
            ->setTitle('Products')
            ->setDescription('Product overview')
            ->setCanonicalUrl('https://example.com/products')
            ->addAlternateUrl('cs', 'https://example.com/cs/produkty')
            ->setMeta('author', 'Websonette')
            ->setProperty('og:title', 'Products');

        $html = $this->renderControl($page);

        self::assertStringContainsString('<title', $html);
        self::assertStringContainsString('Products</title>', $html);
        self::assertStringContainsString('name="description"', $html);
        self::assertStringContainsString('content="Product overview"', $html);
        self::assertStringContainsString('rel="canonical"', $html);
        self::assertStringContainsString('href="https://example.com/products"', $html);
        self::assertStringContainsString('hreflang="cs"', $html);
        self::assertStringContainsString('name="author"', $html);
        self::assertStringContainsString('property="og:title"', $html);
        self::assertStringNotContainsString('<div', $html);
        self::assertStringNotContainsString('data-websonette-component', $html);
    }

    public function testRendersStableSnippetTargetsAndEscapesValues(): void
    {
        $page = (new PageContext())
            ->setTitle('<script>alert(1)</script>')
            ->setDescription('Description "quoted"')
            ->setCanonicalUrl('https://example.com/?a=1&b=2')
            ->setIndexable(false)
            ->setFollowable(false);

        $html = $this->renderControl($page);

        self::assertStringContainsString('id="snippet-pageContextHtmlHead-title"', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringContainsString('Description &quot;quoted&quot;', $html);
        self::assertStringContainsString('https://example.com/?a=1&amp;b=2', $html);
        self::assertStringContainsString('noindex, nofollow', $html);
    }

    private function renderControl(PageContext $page): string
    {
        $control = new PageContextHtmlHeadControl($page);
        $control->setTemplateFactory($this->createTemplateFactory());
        $presenter = new class extends Presenter {};
        $presenter->injectPrimary(
            new \Nette\Http\Request(new \Nette\Http\UrlScript('http://localhost/')),
            new \Nette\Http\Response(),
        );
        $presenter->addComponent($control, 'pageContextHtmlHead');

        ob_start();
        try {
            $control->render();
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private function createTemplateFactory(): TemplateFactory
    {
        $latteFactory = new class implements LatteFactory {
            public function create(?Control $control = null): Engine
            {
                $engine = new Engine();
                $cacheDir = sys_get_temp_dir() . '/websonette-latte-application-latte';
                if (!is_dir($cacheDir)) {
                    mkdir($cacheDir, 0777, true);
                }
                $engine->setTempDirectory($cacheDir);
                if ($control !== null) {
                    $engine->addExtension(new UIExtension($control));
                }

                return $engine;
            }
        };

        return new TemplateFactory($latteFactory);
    }
}