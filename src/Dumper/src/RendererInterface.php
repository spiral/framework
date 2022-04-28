<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

/**
 * Applies set of styles to value dump.
 *
 * @deprecated since v2.13. Will be removed in v3.0
 */
interface RendererInterface
{
    /**
     * Wraps dump content with overlay container.
     *
     * @return string
     */
    public function wrapContent(string $body): ?string;

    /**
     * Generates indent string (tabs or spaces).
     */
    public function indent(int $level): string;

    /**
     * Stylize content using pre-defined style.
     *
     * @param string|null $element
     */
    public function apply($element, string $type, string $context = ''): string;

    /**
     * Must return true if strings has to be escaped by Dumper.
     */
    public function escapeStrings(): bool;
}
