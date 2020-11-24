<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var mixed|NodeInterface */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed|NodeInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function trimValue(): array
    {
        $value = $this->value;
        if ($value instanceof Nil) {
            return [];
        }

        if (is_string($value)) {
            return [new Raw(trim($value, '\'"'))];
        }

        if (!$value instanceof Mixin) {
            return [$value];
        }

        // trim mixin quotes
        $nodes = $value->nodes;

        if (count($nodes) >= 3 && $nodes[0] instanceof Raw && $nodes[count($nodes) - 1] instanceof Raw) {
            $quote = $nodes[0]->content[0];
            if (!in_array($quote, ['"', '\''])) {
                return $nodes;
            }

            $nodes[0] = new Raw(ltrim($nodes[0]->content, $quote));
            $nodes[count($nodes) - 1] = new Raw(
                rtrim($nodes[count($nodes) - 1]->content, $quote)
            );
        }

        foreach ($nodes as $index => $node) {
            if ($node instanceof Raw && $node->content === '') {
                unset($nodes[$index]);
            }
        }

        return [new Mixin(array_values($nodes))];
    }
}
