<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Locate all declared and requested properties.
 */
final class LocateProperties extends NodeVisitorAbstract
{
    /** @var array */
    private $properties = [];

    /** @var array */
    private $requested = [];

    /**
     * Get names of all virtual properties.
     */
    public function getProperties(): array
    {
        return array_values(array_diff(
            array_values($this->requested),
            array_values($this->properties)
        ));
    }

    /**
     * Detected declared and requested nodes.
     *
     * @return int|null|Node
     */
    public function enterNode(Node $node)
    {
        if (
            $node instanceof Node\Expr\PropertyFetch &&
            $node->var instanceof Node\Expr\Variable &&
            $node->var->name === 'this'
        ) {
            $this->requested[$node->name->name] = $node->name->name;
        }

        if ($node instanceof Node\Stmt\Property) {
            foreach ($node->props as $prop) {
                if ($prop instanceof Node\Stmt\PropertyProperty) {
                    $this->properties[$prop->name->name] = $prop->name->name;
                }
            }
        }

        return null;
    }
}
