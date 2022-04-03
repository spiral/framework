<?php

declare(strict_types=1);

namespace Spiral\Translator\Exception;

/**
 * Invalid or unknown locale.
 */
class LocaleException extends TranslatorException
{
    public function __construct(
        protected string $locale,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(\sprintf('Undefined locale \'%s\'', $locale), $code, $previous);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
