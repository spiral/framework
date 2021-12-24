<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\Renderer;

/**
 * No styles.
 */
final class PlainRenderer extends AbstractRenderer
{
    /** @var bool */
    private $escapeStrings = false;

    public function __construct(bool $escapeStrings = true)
    {
        $this->escapeStrings = $escapeStrings;
    }

    /**
     * @inheritdoc
     */
    public function apply($element, string $type, string $context = ''): string
    {
        return (string)$element;
    }

    /**
     * @inheritdoc
     */
    public function escapeStrings(): bool
    {
        return $this->escapeStrings;
    }
}
