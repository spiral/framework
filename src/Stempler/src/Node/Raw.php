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
 * Plain text or comment. Might contain inclusion of other syntaxes within it.
 */
final class Raw implements NodeInterface
{
    use ContextTrait;

    /** @var string */
    public $content;

    /**
     * @param string       $content
     * @param Context|null $context
     */
    public function __construct(string $content, Context $context = null)
    {
        $this->content = $content;
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
