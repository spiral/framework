<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Context;

use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\VisitorContext;

final class StackContext
{
    /** @var VisitorContext */
    private $ctx;

    /**
     * @param VisitorContext $ctx
     */
    private function __construct(VisitorContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * @param Aggregate $aggregate
     * @param int       $level
     */
    public function register(Aggregate $aggregate, int $level = 0): void
    {
        // collect all stack withing specific scope
        $node = $this->getStackRootNode($level);

        $stacks = $node->getAttribute(self::class, []);
        $stacks[] = $aggregate;
        $node->setAttribute(self::class, $stacks);
    }

    /**
     * @param string      $name
     * @param Tag         $child
     * @param string|null $uniqueID
     * @return bool
     */
    public function push(string $name, Tag $child, string $uniqueID = null): bool
    {
        foreach ($this->getStacks() as $stack) {
            if ($stack->accepts($name) !== $name) {
                continue;
            }

            if ($uniqueID !== null && isset($stack->uniqueIDs[$uniqueID])) {
                return true;
            }
            $stack->uniqueIDs[$uniqueID] = true;

            foreach ($child->nodes as $child) {
                $stack->nodes[] = $child;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string      $name
     * @param Tag         $child
     * @param string|null $uniqueID
     * @return bool
     */
    public function prepend(string $name, Tag $child, string $uniqueID = null): bool
    {
        foreach ($this->getStacks() as $stack) {
            if ($stack->accepts($name) !== $name) {
                continue;
            }

            if ($uniqueID !== null && isset($stack->uniqueIDs[$uniqueID])) {
                return true;
            }
            $stack->uniqueIDs[$uniqueID] = true;

            foreach ($child->nodes as $child) {
                array_unshift($stack->nodes, $child);
            }

            return true;
        }

        return false;
    }

    /**
     * Return all stacks available for the current path.
     *
     * @return Aggregate[]
     */
    public function getStacks(): array
    {
        $stacks = [];
        foreach (array_reverse($this->ctx->getScope()) as $node) {
            if ($node instanceof AttributedInterface) {
                foreach ($node->getAttribute(self::class, []) as $stack) {
                    $stacks[] = $stack;
                }
            }
        }

        return $stacks;
    }

    /**
     * @param VisitorContext $ctx
     * @return StackContext
     */
    public static function on(VisitorContext $ctx): self
    {
        return new self($ctx);
    }

    /**
     * @param int $level
     * @return AttributedInterface
     */
    private function getStackRootNode(int $level): AttributedInterface
    {
        if ($level === 0) {
            $node = $this->ctx->getParentNode();
        } else {
            $scope = $this->ctx->getScope();

            // looking for the parent node via given nesting level
            $node = $scope[count($scope) - 2 - $level] ?? $this->ctx->getFirstNode();
        }

        if (!$node instanceof AttributedInterface) {
            throw new \LogicException(
                sprintf(
                    'Unable to create import on node without attribute storage (%s)',
                    is_object($node) ? get_class($node) : gettype($node)
                )
            );
        }

        return $node;
    }
}
