<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node\Dynamic;

use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

final class Directive implements NodeInterface
{
    use ContextTrait;

    /** @var string */
    public $name;

    /** @var string|null */
    public $body;

    /** @var array */
    public $values = [];

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
        yield from [];
    }
}
