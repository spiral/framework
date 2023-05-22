<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Node\NodeInterface;

/**
 * Inspired by php-parser.
 *
 * @see https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/NodeTraverser.php
 */
final class Traverser
{
    /** @var VisitorInterface[] */
    private array $visitors = [];
    private bool $stopTraversal = false;

    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     */
    public function addVisitor(VisitorInterface $visitor): void
    {
        $this->visitors[] = $visitor;
    }

    public function removeVisitor(VisitorInterface $visitor): void
    {
        foreach ($this->visitors as $index => $added) {
            if ($added === $visitor) {
                unset($this->visitors[$index]);
                break;
            }
        }
    }

    /**
     * Traverses an array of nodes using added visitors.
     *
     * @template TNode of NodeInterface
     *
     * @param TNode[] $nodes
     * @return NodeInterface[]
     * @throws \Throwable
     */
    public function traverse(array $nodes, VisitorContext $context = null): array
    {
        $context ??= new VisitorContext();

        $ctx = clone $context;
        foreach ($nodes as $index => $node) {
            if ($this->stopTraversal) {
                break;
            }

            $traverseChildren = true;
            $breakVisitorID = null;

            if ($node instanceof NodeInterface) {
                $ctx = $context->withNode($node);
            }

            foreach ($this->visitors as $visitorID => $visitor) {
                $result = $visitor->enterNode($node, $ctx);

                switch (true) {
                    case $result === null:
                        break;

                    case $result instanceof NodeInterface:
                        $node = $result;
                        break;

                    case $result === VisitorInterface::DONT_TRAVERSE_CHILDREN:
                        $traverseChildren = false;
                        break;

                    case $result === VisitorInterface::DONT_TRAVERSE_CURRENT_AND_CHILDREN:
                        $traverseChildren = false;
                        $breakVisitorID = $visitorID;
                        break 2;

                    case $result === VisitorInterface::STOP_TRAVERSAL:
                        $this->stopTraversal = true;

                        break 3;

                    default:
                        throw new \LogicException(
                            'enterNode() returned invalid value of type ' . \gettype($result)
                        );
                }
            }

            // sub nodes
            if ($traverseChildren && $node instanceof NodeInterface) {
                $nodes[$index] = $this->traverseNode($node, $ctx);
                if ($this->stopTraversal) {
                    break;
                }
            }

            foreach ($this->visitors as $visitorID => $visitor) {
                $result = $visitor->leaveNode($node, $ctx);

                switch (true) {
                    case $result === null:
                        break;

                    case $result instanceof NodeInterface:
                        $nodes[$index] = $result;
                        break;

                    case $result === VisitorInterface::REMOVE_NODE:
                        unset($nodes[$index]);
                        break;

                    case $result === VisitorInterface::STOP_TRAVERSAL:
                        $this->stopTraversal = true;
                        break 3;

                    default:
                        throw new \LogicException(
                            'leaveNode() returned invalid value of type ' . gettype($result)
                        );
                }

                if ($breakVisitorID === $visitorID) {
                    break;
                }
            }
        }

        return \array_values($nodes);
    }

    /**
     * Recursively traverse a node.
     */
    private function traverseNode(NodeInterface $node, VisitorContext $context): NodeInterface
    {
        $ctx = clone $context;
        foreach ($node as $name => $_) {
            $_child = &$node->$name;
            if (\is_array($_child)) {
                $_child = $this->traverse($_child, $ctx);
                if ($this->stopTraversal) {
                    break;
                }

                continue;
            }

            if (!$_child instanceof NodeInterface) {
                continue;
            }

            $ctx = $context->withNode($_child);

            $traverseChildren = true;
            $breakVisitorID = null;

            foreach ($this->visitors as $visitorID => $visitor) {
                $result = $visitor->enterNode($_child, $ctx);
                switch (true) {
                    case $result === null:
                        break;

                    case $result instanceof NodeInterface:
                        $_child = $result;
                        break;

                    case VisitorInterface::DONT_TRAVERSE_CHILDREN:
                        $traverseChildren = false;
                        break;

                    case VisitorInterface::DONT_TRAVERSE_CURRENT_AND_CHILDREN:
                        $traverseChildren = false;
                        $breakVisitorID = $visitorID;
                        break 2;

                    case VisitorInterface::STOP_TRAVERSAL:
                        $this->stopTraversal = true;
                        break 3;

                    default:
                        throw new \LogicException(
                            'enterNode() returned invalid value of type ' . \gettype($result)
                        );
                }
            }

            if ($traverseChildren) {
                $_child = $this->traverseNode($_child, $ctx);
                if ($this->stopTraversal) {
                    break;
                }
            }

            foreach ($this->visitors as $visitorID => $visitor) {
                $result = $visitor->leaveNode($_child, $ctx);

                switch (true) {
                    case $result === null:
                        break;

                    case $result instanceof NodeInterface:
                        $_child = $result;
                        break;

                    case $result === VisitorInterface::STOP_TRAVERSAL:
                        $this->stopTraversal = true;
                        break 3;

                    default:
                        throw new \LogicException(
                            'leaveNode() returned invalid value of type ' . \gettype($result)
                        );
                }

                if ($breakVisitorID === $visitorID) {
                    break;
                }
            }
        }

        return $node;
    }
}
