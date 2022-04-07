<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Name;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Utils;

/**
 * Add use statement to the code.
 */
final class AddUse extends NodeVisitorAbstract
{
    private ClassNode $node;

    /** @var Node\Stmt\Use_[] */
    private array $nodes = [];

    public function __construct(ClassNode $node)
    {
        $this->node = $node;
    }

    /**
     * @return Node\Stmt\Namespace_|null
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Namespace_) {
            return null;
        }

        $imported = [];
        if (!$this->node->hasConstructor && $this->node->constructorParams) {
            foreach ($this->node->constructorParams as $param) {
                if (!empty($param->type) && $param->type->fullName) {
                    $import = [$param->type->fullName, $param->type->alias];
                    if (in_array($import, $imported, true)) {
                        continue;
                    }

                    $imported[] = $import;
                    $this->nodes[] = $this->buildUse($param->type->fullName, $param->type->alias);
                }
            }
        }

        foreach ($this->node->dependencies as $dependency) {
            $import = [$dependency->type->fullName, $dependency->type->alias];
            if (in_array($import, $imported, true)) {
                continue;
            }

            $imported[] = $import;
            $this->nodes[] = $this->buildUse(
                $dependency->type->fullName,
                $dependency->type->alias
            );
        }

        $placementID = $this->definePlacementID($node);
        $node->stmts = Utils::injectValues(
            $node->stmts,
            $placementID,
            $this->removeDuplicates($node->stmts, $this->nodes)
        );

        return $node;
    }

    private function definePlacementID(Namespace_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Class_) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @param Node\Stmt[]      $stmts
     * @param Node\Stmt\Use_[] $nodes
     * @return Node\Stmt\Use_[]
     */
    private function removeDuplicates(array $stmts, array $nodes): array
    {
        $uses = $this->getExistingUseParts($stmts);

        foreach ($nodes as $i => $node) {
            if (!$node instanceof Node\Stmt\Use_) {
                continue;
            }

            foreach ($node->uses as $use) {
                if (in_array($use->name->parts, $uses, true)) {
                    unset($nodes[$i]);
                }
            }
        }

        return $nodes;
    }

    /**
     * @param Node\Stmt[] $stmts
     *
     * @return string[][]
     *
     * @psalm-return list<non-empty-list<string>>
     */
    private function getExistingUseParts(array $stmts): array
    {
        $uses = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node\Stmt\Use_) {
                continue;
            }

            foreach ($stmt->uses as $use) {
                $uses[] = $use->name->parts;
            }
        }

        return $uses;
    }

    private function buildUse(string $type, ?string $alias = null): Node\Stmt\Use_
    {
        $b = new Use_(new Name($type), Node\Stmt\Use_::TYPE_NORMAL);
        if (!empty($alias)) {
            $b->as($alias);
        }

        return $b->getNode();
    }
}
