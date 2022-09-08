<?php

declare(strict_types=1);

namespace Spiral\Translator\Event;

final class LocaleUpdated
{
    public function __construct(
        public readonly string $locale,
    ) {
    }
}
