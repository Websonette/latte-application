<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI\PageContextHtmlHead;

interface PageContextHtmlHeadControlFactory
{
    public function create(): PageContextHtmlHeadControl;
}