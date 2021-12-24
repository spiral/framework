<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Translator\Exception;

/**
 * Invalid or unknown locale.
 */
class LocaleException extends TranslatorException
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @param string     $locale
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($locale, $code = 0, $previous = null)
    {
        $this->locale = $locale;
        parent::__construct("Undefined locale '{$locale}'", $code, $previous);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
