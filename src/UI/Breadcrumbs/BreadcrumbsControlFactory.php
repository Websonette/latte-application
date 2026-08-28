<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI\Breadcrumbs;

interface BreadcrumbsControlFactory
{
    public function create(): BreadcrumbsControl;
}
