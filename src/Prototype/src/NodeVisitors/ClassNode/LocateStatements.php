<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

/**
 * Pick class's imports.
 */
final class LocateStatements extends NodeVisitorAbstract
{
    private array $imports = [];

    public function enterNode(Node $node): void
    {
        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $this->imports[] = [
                    'name'  => \implode('\\', $use->name->parts),
                    'alias' => $use->alias->name ?? null,
                ];
            }
        }
    }

    public function getImports(): array
    {
        return $this->imports;
    }
}
