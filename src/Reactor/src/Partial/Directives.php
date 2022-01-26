<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\DeclarationInterface;

class Directives implements DeclarationInterface
{
    /** @var string[] */
    private $directives;

    public function __construct(string ...$directives)
    {
        $this->directives = $directives;
    }

    /**
     * {@inheritDoc}
     */
    public function render(int $indentLevel = 0): string
    {
        if (empty($this->directives)) {
            return '';
        }

        return 'declare(' . implode(', ', $this->directives) . ');';
    }
}
