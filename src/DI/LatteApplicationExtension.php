<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\DI;

use Contributte\Translation\DI\TranslationProviderInterface;
use Nette\DI\CompilerExtension;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControlFactory;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControlFactory;
use Websonette\WebApplication\Breadcrumbs\Breadcrumbs;
use Websonette\WebApplication\Page\PageContext;

final class LatteApplicationExtension extends CompilerExtension implements TranslationProviderInterface
{
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition(null)
            ->setFactory(Breadcrumbs::class);

        $builder->addDefinition(null)
            ->setFactory(PageContext::class);

        $builder->addFactoryDefinition(null)
            ->setImplement(BreadcrumbsControlFactory::class);

        $builder->addFactoryDefinition(null)
            ->setImplement(PageContextHtmlHeadControlFactory::class);
    }

    public function getTranslationResources(): array
    {
        return [dirname(__DIR__, 2) . '/resources/translations'];
    }
}