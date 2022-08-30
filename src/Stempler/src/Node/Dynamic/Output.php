<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\Dynamic;

use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * @implements NodeInterface<Output>
 */
final class Output implements NodeInterface
{
    use ContextTrait;

    public bool $rawOutput = false;

    /**
     * Filter must be declared in sprintf format. Example: Slugify::slugify(%s)
     */
    public ?string $filter = null;
    public ?string $body = null;

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
