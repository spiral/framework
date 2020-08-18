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
 */
interface RendererInterface
{
    /**
     * Wraps dump content with overlay container.
     *
     * @param string $body
     * @return string
     */
    public function wrapContent(string $body): ?string;

    /**
     * Generates indent string (tabs or spaces).
     *
     * @param int $level
     * @return string
     */
    public function indent(int $level): string;

    /**
     * Stylize content using pre-defined style.
     *
     * @param string|null $element
     * @param string      $type
     * @param string      $context
     * @return string
     */
    public function apply($element, string $type, string $context = ''): string;

    /**
     * Must return true if strings has to be escaped by Dumper.
     *
     * @return bool
     */
    public function escapeStrings(): bool;
}
