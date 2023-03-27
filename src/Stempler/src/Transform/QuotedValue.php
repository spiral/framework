<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform;

use Spiral\Stempler\Node\HTML\Nil;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Raw;

/**
 * Carries value defined inside via attribute.
 */
final class QuotedValue
{
    public function __construct(
        private readonly NodeInterface|string $value
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array<NodeInterface|string>
     */
    public function trimValue(): array
    {
        $value = $this->value;
        if ($value instanceof Nil) {
            return [];
        }

        if (\is_string($value)) {
            return [new Raw(\trim($value, '\'"'))];
        }

        if (!$value instanceof Mixin) {
            return [$value];
        }

        // trim mixin quotes
        $nodes = $value->nodes;

        if (\count($nodes) >= 3 && $nodes[0] instanceof Raw && $nodes[\count($nodes) - 1] instanceof Raw) {
            /**
             * TODO issue #767
             * @link https://github.com/spiral/framework/issues/767
             * @psalm-suppress InvalidArrayAccess
             */
            $quote = $nodes[0]->content[0];
            if (!\in_array($quote, ['"', "'"])) {
                return $nodes;
            }

            $nodes[0] = new Raw(\ltrim($nodes[0]->content, $quote));
            /**
             * TODO issue #767
             * @link https://github.com/spiral/framework/issues/767
             * @psalm-suppress NoInterfaceProperties
             */
            $content = $nodes[\count($nodes) - 1]->content;
            $nodes[\count($nodes) - 1] = new Raw(
                \rtrim($content, $quote)
            );
        }

        foreach ($nodes as $index => $node) {
            if ($node instanceof Raw && $node->content === '') {
                unset($nodes[$index]);
            }
        }

        return [new Mixin(\array_values($nodes))];
    }
}
