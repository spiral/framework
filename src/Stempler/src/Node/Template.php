<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\AttributeTrait;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Top level template node.
 */
final class Template implements NodeInterface, AttributedInterface
{
    use AttributeTrait;
    use ContextTrait;

    /** @var NodeInterface[] */
    public $nodes = [];

    public function __construct(array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @param Context|null $context
     */
    public function setContext(Context $context = null): void
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): ?Context
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
