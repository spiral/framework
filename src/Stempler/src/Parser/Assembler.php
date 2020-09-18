<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Parser;

use Spiral\Stempler\Node\NodeInterface;

/**
 * Assembles node tree by keeping current node context.
 */
final class Assembler
{
    /** @var NodeInterface */
    private $node;

    /** @var string */
    private $path;

    /** @var NodeInterface[] */
    private $stack = [];

    /**
     * @param NodeInterface $node
     * @param string        $path
     */
    public function __construct(NodeInterface $node, string $path)
    {
        $this->node = $node;
        $this->path = $path;
    }

    /**
     * @return NodeInterface
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    /**
     * @return string
     */
    public function getStackPath(): string
    {
        $path = [$this->nodeName($this->node)];
        foreach ($this->stack as $tuple) {
            $path[] = $this->nodeName($tuple[0]);
        }

        return implode('.', array_reverse($path));
    }

    /**
     * @param NodeInterface $node
     */
    public function push(NodeInterface $node): void
    {
        $this->node->{$this->path}[] = $node;
    }

    /**
     * @param NodeInterface $node
     * @param string        $path
     */
    public function open(NodeInterface $node, string $path): void
    {
        $this->push($node);

        array_push($this->stack, [$this->node, $this->path]);
        $this->node = $node;
        $this->path = $path;
    }

    /**
     * Close stack.
     */
    public function close(): void
    {
        [$this->node, $this->path] = array_pop($this->stack);
    }

    /**
     * @param NodeInterface $node
     * @return string
     */
    private function nodeName(NodeInterface $node): string
    {
        $r = new \ReflectionClass($node);
        if (property_exists($node, 'name')) {
            return lcfirst($r->getShortName()) . "[{$node->name}]";
        }

        return lcfirst($r->getShortName());
    }
}
