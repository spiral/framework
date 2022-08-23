<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Compiler\Renderer;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\RendererInterface;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\PHP;

final class PHPRenderer implements RendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(Compiler $compiler, Compiler\Result $result, NodeInterface $node): bool
    {
        if ($node instanceof PHP) {
            $result->push($node->content, $node->getContext());
            return true;
        }

        return false;
    }
}
