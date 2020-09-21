<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var string */
    private $trim;

    /**
     * @param string $charset
     */
    public function __construct(string $charset = " \n\t\r")
    {
        $this->trim = $charset;
    }

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
        if ($node instanceof Raw && trim($node->content, $this->trim) === '') {
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
