<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\UI;

enum FlashMessage: string
{
    case Error = 'error';
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';

    public function translationKey(): string
    {
        return 'websonette.latte-application.flash.' . $this->value;
    }
}
