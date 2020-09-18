<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Visitor;

use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Transform\Context\StackContext;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Declares stack aggregate placeholders.
 */
final class DefineStacks implements VisitorInterface
{
    /** @var string */
    private $stackKeyword = 'stack:collect';

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Tag && strpos($node->name, $this->stackKeyword) === 0) {
            return $this->registerAggregate(StackContext::on($ctx), $node);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * @param StackContext $ctx
     * @param Tag          $node
     * @return Aggregate|Tag
     */
    private function registerAggregate(StackContext $ctx, Tag $node)
    {
        $name = $this->stackName($node);
        if ($name === null) {
            return $node;
        }

        $stack = new Aggregate($node->getContext());
        $stack->pattern = sprintf('include:%s', $name);
        $stack->nodes = $node->nodes;

        $ctx->register($stack, $this->stackLevel($node));

        return $stack;
    }

    /**
     * @param Tag $tag
     * @return string|null
     */
    private function stackName(Tag $tag): ?string
    {
        $options = [];
        foreach ($tag->attrs as $attr) {
            if (is_string($attr->value)) {
                $options[$attr->name] = trim($attr->value, '\'"');
            }
        }

        return $options['name'] ?? null;
    }

    /**
     * @param Tag $tag
     * @return int
     */
    private function stackLevel(Tag $tag): int
    {
        $options = [];
        foreach ($tag->attrs as $attr) {
            if (is_string($attr->value)) {
                $options[$attr->name] = trim($attr->value, '\'"');
            }
        }

        return abs((int)($options['level'] ?? 0));
    }
}
