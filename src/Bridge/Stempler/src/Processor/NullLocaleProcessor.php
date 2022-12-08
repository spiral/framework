<?php

declare(strict_types=1);

namespace Spiral\Stempler\Processor;

use Spiral\Views\ContextInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * The processor is used to remove brackets [[ ... ]] when the Translator component is not installed.
 */
final class NullLocaleProcessor implements ProcessorInterface
{
    private const REGEXP = '/\[\[(.*?)\]\]/s';

    public function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        //We are not forcing locale for now
        return $source->withCode(
            \preg_replace_callback(
                self::REGEXP,
                static fn ($matches) => $matches[1],
                $source->getCode()
            )
        );
    }
}
