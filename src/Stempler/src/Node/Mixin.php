<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Combines
 */
final class Mixin implements NodeInterface
{
    use ContextTrait;

    /** @var NodeInterface[] */
    public $nodes = [];

    /**
     * @param array        $nodes
     * @param Context|null $context
     */
    public function __construct(array $nodes = [], Context $context = null)
    {
        $this->nodes = $nodes;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
