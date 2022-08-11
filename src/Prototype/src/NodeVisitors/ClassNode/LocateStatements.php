<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Pick class's imports.
 */
final class LocateStatements extends NodeVisitorAbstract
{
    /** @var array */
    private $imports = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->imports[] = [
                    'name'  => implode('\\', $use->name->parts),
                    'alias' => $use->alias->name ?? null,
                ];
            }
        }

        return null;
    }

    public function getImports(): array
    {
        return $this->imports;
    }
}
