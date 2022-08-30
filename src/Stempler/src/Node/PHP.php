<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Static PHP block.
 *
 * @implements NodeInterface<PHP>
 */
final class PHP implements NodeInterface
{
    use ContextTrait;

    public const ORIGINAL_BODY = 'PHP_BODY';

    public function __construct(
        public string $content,
        /** @internal */
        public array $tokens,
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
