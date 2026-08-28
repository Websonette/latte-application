<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI\PageContextHtmlHead;

use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Websonette\WebApplication\Page\PageContext;

/**
 * Renders PageContext into an HTML head without an invalid flow-content wrapper.
 */
final class PageContextHtmlHeadControl extends Control
{
    public function __construct(
        private readonly PageContext $pageContext,
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
        $template->add('pageContext', $this->pageContext);

        return $template;
    }
}