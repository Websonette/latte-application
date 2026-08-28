<?php

declare(strict_types=1);

namespace App\Presentation;

use Websonette\LatteApplication\Bootstrap;
use Websonette\LatteApplication\Presenter\WebBasePresenter;

final class HomePresenter extends WebBasePresenter
{
    public function renderDefault(): void
    {
        $this->loadBootstrapPresenter();
        $this->loadBreadcrumbsExample();
    }

    private function loadBootstrapPresenter()
    {
        $this->getTemplate()->packageBootstrap = Bootstrap::class;
        $this->getTemplate()->packagePresenter = WebBasePresenter::class;
    }

    private function loadBreadcrumbsExample()
    {
        $this->breadcrumbs->add(
            title: 'Homepage',
            url: $this->link('this'),
            icon: 'home',
            description: 'Development app home',
        );
    }

    public function handleShowFlash(): void
    {
        $this->flashMessage('Flash message comes from WebBasePresenter.');
        $this->redirect('this');
    }
}
