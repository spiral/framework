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
 * Static PHP block.
 */
final class PHP implements NodeInterface
{
    use ContextTrait;

    public const ORIGINAL_BODY = 'PHP_BODY';

    /** @var string */
    public $content;

    /** @var array @internal */
    public $tokens;

    /**
     * @param string       $content
     * @param array        $tokens
     * @param Context|null $context
     */
    public function __construct(string $content, array $tokens, Context $context = null)
    {
        $this->content = $content;
        $this->tokens = $tokens;
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
