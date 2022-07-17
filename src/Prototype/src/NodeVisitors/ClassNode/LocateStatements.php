<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node\Stmt\Use_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Pick class's imports.
 */
final class LocateStatements extends NodeVisitorAbstract
{
    private array $imports = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
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
