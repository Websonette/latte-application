<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI\Breadcrumbs;

use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Websonette\WebApplication\Breadcrumbs\Breadcrumbs;
use Websonette\LatteApplication\UI\BaseControl;

final class BreadcrumbsControl extends BaseControl
{
    public function __construct(
        private readonly Breadcrumbs $breadcrumbs,
    ) {
    }

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/templates/default.latte');
        $this->getTemplate()->render();
    }

    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);
        assert($template instanceof DefaultTemplate);
        $template->add('breadcrumbs', $this->breadcrumbs);

        return $template;
    }
}
