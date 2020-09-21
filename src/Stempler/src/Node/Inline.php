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

final class Inline implements NodeInterface
{
    use ContextTrait;

    /** @var string */
    public $name;

    /** @var Mixed|string|null */
    public $value;

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
