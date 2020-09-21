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
 * Blocks used to extend and import templates. Block operate as template variable.
 */
final class Block implements NodeInterface, AttributedInterface
{
    use ContextTrait;
    use AttributeTrait;

    /** @var string */
    public $name;

    /** @var NodeInterface[] */
    public $nodes = [];

    /**
     * @param string       $name
     * @param Context|null $context
     */
    public function __construct(string $name, Context $context = null)
    {
        $this->name = $name;
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
