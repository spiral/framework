<?php

declare(strict_types=1);

namespace Spiral\Stempler\Compiler\Renderer;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\RendererInterface;
use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\Hidden;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Node\Template;

final class CoreRenderer implements RendererInterface
{
    public function render(
        Compiler $compiler,
        Compiler\Result $result,
        NodeInterface $node
    ): bool {
        switch (true) {
            case $node instanceof Hidden:
                return true;

            case $node instanceof Template || $node instanceof Block || $node instanceof Aggregate:
                $result->withinContext(
                    $node->getContext(),
                    function (Compiler\Result $source) use ($node, $compiler): void {
                        foreach ($node->nodes as $child) {
                            $compiler->compile($child, $source);
                        }
                    }
                );

                return true;

            case $node instanceof Mixin:
                $result->withinContext(
                    $node->getContext(),
                    function (Compiler\Result $source) use ($node, $compiler): void {
                        foreach ($node->nodes as $child) {
                            if (\is_string($child)) {
                                $source->push($child, null);
                                continue;
                            }

                            $compiler->compile($child, $source);
                        }
                    }
                );

                return true;

            case $node instanceof Raw:
                $result->push($node->content, $node->getContext());

                return true;

            default:
                return false;
        }
    }
}
