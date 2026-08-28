<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests\UI\Breadcrumbs;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use PHPUnit\Framework\TestCase;
use Websonette\WebApplication\Breadcrumbs\Breadcrumbs;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControl;

final class BreadcrumbsControlTest extends TestCase
{
    public function testRendersWebsonetteComponentAttribute(): void
    {
        $html = $this->renderControl($this->breadcrumbsWithItems());

        self::assertStringContainsString('data-websonette-component="Breadcrumbs"', $html);
    }

    public function testRendersItemsInInsertionOrderWithLinksAndActiveLastItem(): void
    {
        $html = $this->renderControl($this->breadcrumbsWithItems());

        self::assertMatchesRegularExpression(
            '/<a class="breadcrumbs__link" href="\/">\s*<span class="breadcrumbs__title">Home<\/span>\s*<\/a>.*'
            . '<a class="breadcrumbs__link" href="\/products">\s*<span class="breadcrumbs__title">Products<\/span>\s*<\/a>.*'
            . '<span class="breadcrumbs__title">Detail<\/span>/s',
            $html,
        );
        self::assertStringContainsString('aria-current="page"', $html);
        self::assertStringContainsString('breadcrumbs__item--current', $html);
        self::assertStringNotContainsString('href="/products/42"', $html);
    }

    public function testRendersIconAndDescription(): void
    {
        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->add('Home', '/', 'home', 'Start here');
        $breadcrumbs->add('Products', '/products', 'box', 'Product overview');

        $html = $this->renderControl($breadcrumbs);

        self::assertStringContainsString('data-icon="home"', $html);
        self::assertStringContainsString('data-icon="box"', $html);
        self::assertStringContainsString('class="breadcrumbs__icon"', $html);
        self::assertStringContainsString('Start here', $html);
        self::assertStringContainsString('Product overview', $html);
        self::assertStringContainsString('class="breadcrumbs__description"', $html);
    }

    public function testOmitsOptionalIconAndDescription(): void
    {
        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->add('Home', '/');

        $html = $this->renderControl($breadcrumbs);

        self::assertStringNotContainsString('breadcrumbs__icon', $html);
        self::assertStringNotContainsString('data-icon', $html);
        self::assertStringNotContainsString('breadcrumbs__description', $html);
    }

    public function testEmptyCollectionKeepsShellAndOmitsNav(): void
    {
        $html = $this->renderControl(new Breadcrumbs());

        self::assertStringContainsString('data-websonette-component="Breadcrumbs"', $html);
        self::assertStringNotContainsString('<nav', $html);
        self::assertStringNotContainsString('breadcrumbs__list', $html);
    }

    public function testEscapesTitleUrlIconAndDescription(): void
    {
        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->add(
            title: 'Home <script>alert(1)</script>',
            url: '/path?a=1&b="q"',
            icon: '"><img src=x onerror=alert(1)>',
            description: 'Desc <b>bold</b>',
        );
        $breadcrumbs->add('Current <em>page</em>', '/current');

        $html = $this->renderControl($breadcrumbs);

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringNotContainsString('<b>bold</b>', $html);
        self::assertStringNotContainsString('<em>page</em>', $html);
        self::assertStringNotContainsString('<img src=x', $html);
        self::assertStringContainsString('Home &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringContainsString('Desc &lt;b&gt;bold&lt;/b&gt;', $html);
        self::assertStringContainsString('Current &lt;em&gt;page&lt;/em&gt;', $html);
        self::assertStringContainsString('href="/path?a=1&amp;b=&quot;q&quot;"', $html);
        self::assertStringContainsString('data-icon="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;"', $html);
    }

    public function testUsesDefaultTemplateAndDoesNotMutateCollection(): void
    {
        $breadcrumbs = $this->breadcrumbsWithItems();
        $control = $this->createControl($breadcrumbs);

        ob_start();
        try {
            $control->render();
            $html = (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $expectedTemplate = dirname((string) (new \ReflectionClass(BreadcrumbsControl::class))->getFileName())
            . '/templates/default.latte';

        self::assertFileExists($expectedTemplate);
        self::assertSame(
            realpath($expectedTemplate),
            realpath((string) $control->getTemplate()->getFile()),
        );
        self::assertStringContainsString('aria-label="Breadcrumb"', $html);
        self::assertCount(3, $breadcrumbs);
        self::assertTrue($breadcrumbs->has('/products/42'));
    }

    private function breadcrumbsWithItems(): Breadcrumbs
    {
        $breadcrumbs = new Breadcrumbs();
        $breadcrumbs->add('Home', '/', 'home', 'Introduction');
        $breadcrumbs->add('Products', '/products');
        $breadcrumbs->add('Detail', '/products/42', 'box', 'Product detail');

        return $breadcrumbs;
    }

    private function renderControl(Breadcrumbs $breadcrumbs): string
    {
        $control = $this->createControl($breadcrumbs);

        ob_start();
        try {
            $control->render();
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    private function createControl(Breadcrumbs $breadcrumbs): BreadcrumbsControl
    {
        $control = new BreadcrumbsControl($breadcrumbs);
        $control->setTemplateFactory($this->createTemplateFactory());
        $presenter = new class extends Presenter {};
        $presenter->injectPrimary(
            new \Nette\Http\Request(new \Nette\Http\UrlScript('http://localhost/')),
            new \Nette\Http\Response(),
        );
        $presenter->addComponent($control, 'breadcrumbs');

        return $control;
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
