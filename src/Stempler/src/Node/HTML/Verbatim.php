<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\AttributeTrait;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Non HTML codebase (JS or CSS).
 */
final class Verbatim implements NodeInterface, AttributedInterface
{
    use ContextTrait;
    use AttributeTrait;

    /** @var NodeInterface[] */
    public array $nodes = [];

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
