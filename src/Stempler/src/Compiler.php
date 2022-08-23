<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Compiler\RendererInterface;
use Spiral\Stempler\Compiler\Result;
use Spiral\Stempler\Exception\CompilerException;
use Spiral\Stempler\Node\NodeInterface;

/**
 * Recursively compile node tree using set of handlers.
 */
final class Compiler
{
    /** @var RendererInterface[] */
    private $renders = [];

    public function addRenderer(RendererInterface $renderer): void
    {
        $this->renders[] = $renderer;
    }

    /**
     * @param NodeInterface|array $node
     * @param Result|null         $result
     */
    public function compile($node, Result $result = null): Result
    {
        $result = $result ?? new Result();

        if (is_array($node)) {
            foreach ($node as $child) {
                $this->compile($child, $result);
            }

            return $result;
        }

        foreach ($this->renders as $renderer) {
            if ($renderer->render($this, $result, $node)) {
                return $result;
            }
        }

        throw new CompilerException(
            sprintf('Unable to compile %s, no renderer found', get_class($node)),
            $node->getContext()
        );
    }
}
