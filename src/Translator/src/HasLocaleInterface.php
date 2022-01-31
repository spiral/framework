<?php

declare(strict_types=1);

namespace Spiral\Translator;

interface HasLocaleInterface
{
    public function getLocale(): string;
}
