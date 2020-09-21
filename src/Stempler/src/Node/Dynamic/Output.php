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

final class Output implements NodeInterface
{
    use ContextTrait;

    /** @var bool */
    public $rawOutput = false;

    /**
     * Filter must be declared in sprintf format. Example: Slugify::slugify(%s)
     *
     * @var string|null
     */
    public $filter;

    /** @var string|null */
    public $body;

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
