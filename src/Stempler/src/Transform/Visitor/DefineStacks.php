<?php

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
    private string $stackKeyword = 'stack:collect';

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($node instanceof Tag && \str_starts_with($node->name, $this->stackKeyword)) {
            return $this->registerAggregate(StackContext::on($ctx), $node);
        }

        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    private function registerAggregate(StackContext $ctx, Tag $node): Aggregate|Tag
    {
        $name = $this->stackName($node);
        if ($name === null) {
            return $node;
        }

        $stack = new Aggregate($node->getContext());
        $stack->pattern = \sprintf('include:%s', $name);
        $stack->nodes = $node->nodes;

        $ctx->register($stack, $this->stackLevel($node));

        return $stack;
    }

    private function stackName(Tag $tag): ?string
    {
        $options = [];
        foreach ($tag->attrs as $attr) {
            if (\is_string($attr->value)) {
                $options[$attr->name] = \trim($attr->value, '\'"');
            }
        }

        return $options['name'] ?? null;
    }

    private function stackLevel(Tag $tag): int
    {
        $options = [];
        foreach ($tag->attrs as $attr) {
            if (\is_string($attr->value)) {
                $options[$attr->name] = \trim($attr->value, '\'"');
            }
        }

        return \abs((int)($options['level'] ?? 0));
    }
}
