<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var string */
    private $pushKeyword = 'stack:push';

    /** @var string */
    private $prependKeyword = 'stack:prepend';

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
        if ($node instanceof Tag && strpos($node->name, $this->pushKeyword) === 0) {
            return $this->registerPush(StackContext::on($ctx), $node);
        }

        if ($node instanceof Tag && strpos($node->name, $this->prependKeyword) === 0) {
            return $this->registerPrepend(StackContext::on($ctx), $node);
        }

        return null;
    }

    /**
     * @param StackContext $ctx
     * @param Tag          $node
     * @return int|Tag
     */
    private function registerPush(StackContext $ctx, Tag $node)
    {
        $name = $this->stackName($node);

        if ($name === null || !$ctx->push($name, $node, $this->uniqueID($node))) {
            return null;
        }

        return self::REMOVE_NODE;
    }

    /**
     * @param StackContext $ctx
     * @param Tag          $node
     * @return int|Tag
     */
    private function registerPrepend(StackContext $ctx, Tag $node)
    {
        $name = $this->stackName($node);

        if ($name === null || !$ctx->prepend($name, $node, $this->uniqueID($node))) {
            return null;
        }

        return self::REMOVE_NODE;
    }

    /**
     * @param Tag $tag
     * @return string|null
     */
    private function stackName(Tag $tag): ?string
    {
        foreach ($tag->attrs as $attr) {
            if (is_string($attr->value) && $attr->name === 'name') {
                return trim($attr->value, '\'"');
            }
        }

        return null;
    }

    /**
     * @param Tag $tag
     * @return string|null
     */
    private function uniqueID(Tag $tag): ?string
    {
        foreach ($tag->attrs as $attr) {
            if (is_string($attr->value) && $attr->name === 'unique-id') {
                return trim($attr->value, '\'"');
            }
        }

        return null;
    }
}
