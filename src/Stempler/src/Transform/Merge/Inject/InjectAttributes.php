<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Nil;
use Spiral\Stempler\Node\HTML\Verbatim;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\BlockClaims;
use Spiral\Stempler\Transform\QuotedValue;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Creates attribute values based on un-claimed import blocks via `attr:aggregate` attribute.
 */
final class InjectAttributes implements VisitorInterface
{
    /** @var BlockClaims */
    private $blocks;

    /**
     * @param BlockClaims $blocks
     */
    public function __construct(BlockClaims $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if (!$node instanceof Aggregate) {
            return null;
        }

        foreach ($this->blocks->getUnclaimed() as $name) {
            $alias = $node->accepts($name);
            if ($alias === null) {
                continue;
            }

            $value = $this->blocks->claim($name);

            if ($value instanceof QuotedValue) {
                $node->nodes[] = new Attr($alias, $value->getValue());
                continue;
            }

            // simple copy attribute copy
            if ($value instanceof Attr) {
                $node->nodes[] = clone $value;
                continue;
            }

            $node->nodes[] = new Attr($alias, $this->wrapValue($value));
        }
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * @param array $value
     * @return array|Nil|Mixin
     */
    private function wrapValue($value)
    {
        if ($value === [] || $value === null || $value instanceof Nil) {
            return new Nil();
        }

        if ($value instanceof Verbatim || is_scalar($value)) {
            return $value;
        }

        // auto-quote
        return new Mixin(array_merge(
            [new Raw('"')],
            is_array($value) ? $value : [$value],
            [new Raw('"')]
        ));
    }
}
