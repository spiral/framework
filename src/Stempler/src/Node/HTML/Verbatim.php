<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    public $nodes = [];

    /**
     * @param Context|null $context
     */
    public function __construct(Context $context = null)
    {
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
