<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Tests\UI\Breadcrumbs;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Websonette\WebApplication\Breadcrumbs\Breadcrumbs;
use Websonette\WebApplication\Page\PageContext;
use Websonette\LatteApplication\DI\LatteApplicationExtension;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControl;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControlFactory;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControl;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControlFactory;

final class BreadcrumbsExtensionTest extends TestCase
{
    public function testRegistersBreadcrumbsServiceAndControlFactory(): void
    {
        $container = $this->createContainer();

        $breadcrumbs = $container->getByType(Breadcrumbs::class);
        $factory = $container->getByType(BreadcrumbsControlFactory::class);
        $control = $factory->create();
        $pageContext = $container->getByType(PageContext::class);
        $pageHeadFactory = $container->getByType(PageContextHtmlHeadControlFactory::class);
        $pageHeadControl = $pageHeadFactory->create();

        self::assertInstanceOf(Breadcrumbs::class, $breadcrumbs);
        self::assertInstanceOf(BreadcrumbsControlFactory::class, $factory);
        self::assertInstanceOf(BreadcrumbsControl::class, $control);
        self::assertSame($breadcrumbs, (new ReflectionProperty(BreadcrumbsControl::class, 'breadcrumbs'))->getValue($control));
        self::assertSame($breadcrumbs, $container->getByType(Breadcrumbs::class));
        self::assertInstanceOf(PageContext::class, $pageContext);
        self::assertInstanceOf(PageContextHtmlHeadControlFactory::class, $pageHeadFactory);
        self::assertInstanceOf(PageContextHtmlHeadControl::class, $pageHeadControl);
        self::assertSame($pageContext, (new ReflectionProperty(PageContextHtmlHeadControl::class, 'pageContext'))->getValue($pageHeadControl));
    }

    public function testServiceAndControlWorkThroughContainer(): void
    {
        $container = $this->createContainer();
        $breadcrumbs = $container->getByType(Breadcrumbs::class);
        $control = $container->getByType(BreadcrumbsControlFactory::class)->create();
        $control->setTemplateFactory($this->createTemplateFactory());
        $presenter = new class extends Presenter {};
        $presenter->injectPrimary(
            new \Nette\Http\Request(new \Nette\Http\UrlScript('http://localhost/')),
            new \Nette\Http\Response(),
        );
        $presenter->addComponent($control, 'breadcrumbs');

        $breadcrumbs->add('Home', '/', 'home', 'Introduction');
        $breadcrumbs->add('Products', '/products');

        ob_start();
        try {
            $control->render();
            $html = (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        self::assertSame(2, $breadcrumbs->count());
        self::assertStringContainsString('data-websonette-component="Breadcrumbs"', $html);
        self::assertStringContainsString('href="/"', $html);
        self::assertStringContainsString('Products', $html);
        self::assertStringContainsString('aria-current="page"', $html);
    }

    private function createContainer(): Container
    {
        $tempDir = sys_get_temp_dir() . '/websonette-latte-application-di';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $loader = new ContainerLoader($tempDir, autoRebuild: true);
        $class = $loader->load(static function (Compiler $compiler): ?string {
            $compiler->addExtension('websonette.latte-application', new LatteApplicationExtension());

            return null;
        }, __METHOD__);

        return new $class();
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
