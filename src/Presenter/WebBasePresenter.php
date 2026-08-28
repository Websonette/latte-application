<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Presenter;

use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Websonette\LatteApplication\Frontend\PageMeta;
use Websonette\WebApplication\Breadcrumbs\Breadcrumbs;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControl;
use Websonette\LatteApplication\UI\Breadcrumbs\BreadcrumbsControlFactory;
use Websonette\LatteApplication\UI\FlashMessage;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControl;
use Websonette\LatteApplication\UI\PageContextHtmlHead\PageContextHtmlHeadControlFactory;
use Websonette\WebApplication\Page\PageContext;

abstract class WebBasePresenter extends Presenter
{
    public function __construct(
        private readonly Translator $translator,
        protected readonly Breadcrumbs $breadcrumbs,
        private readonly BreadcrumbsControlFactory $breadcrumbsControlFactory,
        protected readonly PageContext $pageContext,
        private readonly PageContextHtmlHeadControlFactory $pageContextHtmlHeadControlFactory,
    ) {
        parent::__construct();
    }

    /**
     * Publishes application-defined page keys to Latte and, explicitly, to an AJAX navigation payload.
     */
    protected function setWebsonettePageMeta(PageMeta $pageMeta, bool $publishToPayload = false): void
    {
        $this->getTemplate()->websonettePageMeta = $pageMeta;

        if ($publishToPayload) {
            $this->payload->websonettePageMeta = $pageMeta->toArray();
        }
    }

    protected function createComponentBreadcrumbs(): BreadcrumbsControl
    {
        return $this->breadcrumbsControlFactory->create();
    }

    protected function createComponentPageContextHtmlHead(): PageContextHtmlHeadControl
    {
        return $this->pageContextHtmlHeadControlFactory->create();
    }

    protected function redrawPageContextHtmlHead(): void
    {
        $this->getComponent('pageContextHtmlHead')->redrawControl();
    }

    public function flashMessage(
        string|\stdClass|\Stringable $message,
        string|FlashMessage $type = FlashMessage::Success,
        string $description = '',
    ): \stdClass {

        // Set to redraw flashes snippet
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
        }

        if ($message instanceof \stdClass) {

            // Existing flash message
            $flash = $message;

        } else {

            $message = (string) $message;

            // Translate message
            if (str_starts_with($message, '_')) {
                $message = (string) $this->translator->translate(substr($message, 1));
            }

            // Translate description
            if (str_starts_with($description, '_')) {
                $description = (string) $this->translator->translate(substr($description, 1));
            }

            // Flash message type
            $typeValue = $type instanceof FlashMessage ? $type->value : $type;

            // Move message to description and flash label to message if there is no description
            if (trim($description) === '') {
                $description = $message;
                $message = (string) $this->translator->translate(
                    $type instanceof FlashMessage
                        ? $type->translationKey()
                        : 'websonette.latte-application.flash.' . $typeValue,
                );
            }

            // Copied from parent::flashMessage
            $flash = (object) [
                'message' => mb_strtoupper(str_replace("'", '"', $message)),
                'type' => $typeValue,
                'description' => $description,
            ];
        }

        $id = $this->getParameterId('flash');
        $messages = $this->getPresenter()->getFlashSession()->$id;
        $messages[] = $flash;
        $this->getTemplate()->flashes = $messages;
        $this->getPresenter()->getFlashSession()->$id = $messages;

        return $flash;
    }
}
