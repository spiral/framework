<?php

declare(strict_types=1);

namespace Spiral\Reactor;

/**
 * To be rendered with some indent.
 */
interface DeclarationInterface
{
    public const INDENT = '    ';

    /**
     * Must render it's own content into string using given indent level.
     */
    public function render(int $indentLevel = 0): string;
}
