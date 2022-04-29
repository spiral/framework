<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Node\NodeInterface;

/**
 * Assembles node tree by keeping current node context.
 */
final class Assembler
{
    /** @var NodeInterface[] */
    private array $stack = [];

    public function __construct(
        private NodeInterface $node,
        private string $path
    ) {
    }

    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    public function getStackPath(): string
    {
        $path = [$this->nodeName($this->node)];
        foreach ($this->stack as $tuple) {
            $path[] = $this->nodeName($tuple[0]);
        }

        return \implode('.', \array_reverse($path));
    }

    public function push(NodeInterface $node): void
    {
        $this->node->{$this->path}[] = $node;
    }

    public function open(NodeInterface $node, string $path): void
    {
        $this->push($node);

        $this->stack[] = [$this->node, $this->path];
        $this->node = $node;
        $this->path = $path;
    }

    /**
     * Close stack.
     */
    public function close(): void
    {
        [$this->node, $this->path] = \array_pop($this->stack);
    }

    private function nodeName(NodeInterface $node): string
    {
        $r = new \ReflectionClass($node);
        if (\property_exists($node, 'name')) {
            return \lcfirst($r->getShortName()) . \sprintf('[%s]', $node->name);
        }

        return \lcfirst($r->getShortName());
    }
}
