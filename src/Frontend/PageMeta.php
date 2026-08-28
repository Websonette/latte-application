<?php

declare(strict_types=1);

namespace Websonette\LatteApplication\Frontend;

use JsonSerializable;

final readonly class PageMeta implements JsonSerializable
{
    public ?string $module;

    public ?string $presenter;

    public ?string $action;

    public function __construct(
        ?string $module = null,
        ?string $presenter = null,
        ?string $action = null,
    ) {
        $this->module = self::normalize($module);
        $this->presenter = self::normalize($presenter);
        $this->action = self::normalize($action);
    }

    /** @return array{module?: string, presenter?: string, action?: string} */
    public function toArray(): array
    {
        return array_filter(
            [
                'module' => $this->module,
                'presenter' => $this->presenter,
                'action' => $this->action,
            ],
            static fn (?string $value): bool => $value !== null,
        );
    }

    /** @return array{module?: string, presenter?: string, action?: string} */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}