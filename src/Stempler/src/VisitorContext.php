<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Node\NodeInterface;

/**
 * Visitor context contains current node branch with all the previous nodes.
 */
final class VisitorContext
{
    /** @var NodeInterface[] */
    private array $scope = [];

    public function withNode(NodeInterface $node): self
    {
        $context = clone $this;
        $context->scope[] = $node;

        return $context;
    }

    /**
     * @return NodeInterface[]
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    public function getCurrentNode(): ?NodeInterface
    {
        return $this->scope[\count($this->scope) - 1] ?? null;
    }

    public function getParentNode(): ?NodeInterface
    {
        return $this->scope[\count($this->scope) - 2] ?? null;
    }

    public function getFirstNode(): ?NodeInterface
    {
        return $this->scope[0] ?? null;
    }
}
