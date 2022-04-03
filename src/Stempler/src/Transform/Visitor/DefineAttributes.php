<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Visitor;

use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Nil;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Creates node placeholder to aggregate user specific attributes into
 * imported tag.
 */
final class DefineAttributes implements VisitorInterface
{
    public const AGGREGATE_ATTRIBUTE = 'attr:aggregate';

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        if (!$node instanceof Attr || $node->name !== self::AGGREGATE_ATTRIBUTE) {
            return null;
        }

        if ($node->value instanceof Nil) {
            return new Aggregate($node->getContext());
        }

        if (!\is_string($node->value)) {
            return null;
        }

        // expressions like: include:name or prefix:name-
        $pattern = \trim($node->value, '\'"');

        return new Aggregate($node->getContext(), $pattern);
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }
}
