<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Compiler\Renderer;

use PHPUnit\Framework\Constraint\Attribute;
use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\RendererInterface;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Nil;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\NodeInterface;

final class HTMLRenderer implements RendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(Compiler $compiler, Compiler\Result $result, NodeInterface $node): bool
    {
        switch (true) {
            case $node instanceof Tag:
                $this->tag($compiler, $result, $node);
                return true;
            case $node instanceof Attr:
                $this->attribute($compiler, $result, $node);
                return true;
            case $node instanceof Verbatim:
                $this->verbatim($compiler, $result, $node);
                return true;
            default:
                return false;
        }
    }

    /**
     * @param Compiler        $compiler
     * @param Compiler\Result $result
     * @param Tag             $node
     */
    private function tag(Compiler $compiler, Compiler\Result $result, Tag $node): void
    {
        $result->push(sprintf('<%s', $node->name), $node->getContext());

        foreach ($node->attrs as $attr) {
            if (!$attr instanceof Attribute) {
                $compiler->compile($attr, $result);
                continue;
            }

            $this->attribute($compiler, $result, $attr);
        }

        $result->push(sprintf('%s>', $node->void ? '/' : ''), null);

        foreach ($node->nodes as $child) {
            $compiler->compile($child, $result);
        }

        if (!$node->void) {
            $result->push(sprintf('</%s>', $node->name), null);
        }
    }

    /**
     * @param Compiler        $compiler
     * @param Compiler\Result $result
     * @param Attr            $node
     */
    private function attribute(Compiler $compiler, Compiler\Result $result, Attr $node): void
    {
        if ($node->name instanceof NodeInterface) {
            $result->push(' ', null);
            $compiler->compile($node->name, $result);
        } else {
            $result->push(sprintf(' %s', $node->name), $node->getContext());
        }

        $value = $node->value;
        if ($value instanceof Nil) {
            return;
        }

        if ($value instanceof NodeInterface) {
            $result->push('=', null);
            $compiler->compile($value, $result);
            return;
        }

        $result->push(sprintf('=%s', $value), $node->getContext());
    }

    /**
     * @param Compiler        $compiler
     * @param Compiler\Result $result
     * @param Verbatim        $node
     */
    private function verbatim(Compiler $compiler, Compiler\Result $result, Verbatim $node): void
    {
        foreach ($node->nodes as $child) {
            if (is_string($child)) {
                $result->push($child, null);
                continue;
            }

            $compiler->compile($child, $result);
        }
    }
}
