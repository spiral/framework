<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\Dynamic;

use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * @implements NodeInterface<Directive>
 */
final class Directive implements NodeInterface
{
    use ContextTrait;

    public string $name;
    public ?string $body = null;
    public array $values = [];

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
