<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Visitor;

use Spiral\Stempler\Node\Hidden;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

final class DefineHidden implements VisitorInterface
{
    /** @var string */
    private $hiddenKeyword = 'hidden';

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Tag && strpos($node->name, $this->hiddenKeyword) === 0) {
            return new Hidden([$node]);
        }

        return null;
    }
}
