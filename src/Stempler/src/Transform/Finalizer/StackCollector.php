<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Finalizer;

use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Transform\Context\StackContext;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * StackCollection mounts all values pushed to stacks into stack placeholders. You can define stack pushes in any
 * place of your template, including before the stack placeholder but you will have to declare proper scope.
 */
final class StackCollector implements VisitorInterface
{
    private string $pushKeyword = 'stack:push';
    private string $prependKeyword = 'stack:prepend';

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($node instanceof Tag && \str_starts_with($node->name, $this->pushKeyword)) {
            return $this->registerPush(StackContext::on($ctx), $node);
        }

        if ($node instanceof Tag && \str_starts_with($node->name, $this->prependKeyword)) {
            return $this->registerPrepend(StackContext::on($ctx), $node);
        }

        return null;
    }

    private function registerPush(StackContext $ctx, Tag $node): int|Tag|null
    {
        $name = $this->stackName($node);

        if ($name === null || !$ctx->push($name, $node, $this->uniqueID($node))) {
            return null;
        }

        return self::REMOVE_NODE;
    }

    private function registerPrepend(StackContext $ctx, Tag $node): int|Tag|null
    {
        $name = $this->stackName($node);

        if ($name === null || !$ctx->prepend($name, $node, $this->uniqueID($node))) {
            return null;
        }

        return self::REMOVE_NODE;
    }

    private function stackName(Tag $tag): ?string
    {
        foreach ($tag->attrs as $attr) {
            if (\is_string($attr->value) && $attr->name === 'name') {
                return \trim($attr->value, '\'"');
            }
        }

        return null;
    }

    private function uniqueID(Tag $tag): ?string
    {
        foreach ($tag->attrs as $attr) {
            if (\is_string($attr->value) && $attr->name === 'unique-id') {
                return \trim($attr->value, '\'"');
            }
        }

        return null;
    }
}
