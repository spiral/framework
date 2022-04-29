<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Finalizer;

use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Visitor deletes all raw nodes which contain only whitespace characters..
 */
final class TrimRaw implements VisitorInterface
{
    public function __construct(
        private readonly string $characters = " \n\t\r"
    ) {
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        if ($node instanceof Raw && \trim($node->content, $this->characters) === '') {
            foreach ($ctx->getScope() as $scope) {
                if ($scope instanceof Attr) {
                    // do not trim attribute values
                    return null;
                }
            }

            return self::REMOVE_NODE;
        }

        return null;
    }
}
