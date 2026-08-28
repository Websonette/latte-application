<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI;

use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use ReflectionClass;

abstract class BaseControl extends Control
{
    protected function getWebsonetteComponentName(): string
    {
        $shortName = (new ReflectionClass(static::class))->getShortName();

        return str_ends_with($shortName, 'Control')
            ? substr($shortName, 0, -strlen('Control'))
            : $shortName;
    }

    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);
        assert($template instanceof DefaultTemplate);
        $template->add('websonetteComponentName', $this->getWebsonetteComponentName());
        $template->add('componentShell', __DIR__ . '/templates/componentShell.latte');

        return $template;
    }
}
