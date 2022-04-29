<?php

declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Parser\Context;

/**
 * Describes the location of a source line in relation to external path.
 */
final class Location
{
    public function __construct(
        public string $path,
        public int $offset,
        public ?string $grammar = null,
        public ?Location $parent = null
    ) {
    }

    public static function fromContext(Context $context, ?Location $parent = null): Location
    {
        return new self(
            $context->getPath(),
            $context->getToken()->offset,
            $context->getToken()->grammar,
            $parent
        );
    }
}
