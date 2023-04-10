<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Aggregated multiple nodes together using public pattern.
 *
 * @implements NodeInterface<Aggregate>
 * @template TNode of NodeInterface
 */
final class Aggregate implements NodeInterface
{
    use ContextTrait;

    /** @var TNode[] */
    public array $nodes = [];

    public array $uniqueIDs = [];

    public function __construct(
        Context $context = null,
        public string $pattern = '*'
    ) {
        $this->context = $context;
    }

    /**
     * Checks if aggregate can accept given node by it's name and return final name (if prefix presented),
     * or return null.
     */
    public function accepts(string $name): ?string
    {
        if ($this->pattern === '' || $this->pattern === '*') {
            // accept everything
            return $name;
        }

        $conditions = [];
        foreach (\explode(';', $this->pattern) as $condition) {
            if (!\str_contains($condition, ':')) {
                //Invalid
                continue;
            }

            [$option, $value] = \explode(':', \trim($condition));
            $conditions[$option] = $value;
        }

        if (isset($conditions['include'])) {
            $include = \explode(',', $conditions['include']);
            if (\in_array($name, $include)) {
                return $name;
            }

            return null;
        }

        if (isset($conditions['exclude'])) {
            $exclude = \explode(',', $conditions['exclude']);
            if (\in_array($name, $exclude)) {
                return null;
            }

            return $name;
        }

        if (isset($conditions['prefix'])) {
            $conditions['prefix'] = \rtrim($conditions['prefix'], ' *');
            if (\str_starts_with($name, $conditions['prefix'])) {
                return substr($name, \strlen($conditions['prefix']));
            }

            return null;
        }

        return null;
    }

    /**
     * @psalm-suppress LessSpecificImplementedReturnType
     * @return \Generator<'nodes', array<array-key, TNode>, mixed, void>
     */
    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
