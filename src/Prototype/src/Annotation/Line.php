<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Annotation;

/**
 * Singular annotation line.
 */
final class Line
{
    /** @var string */
    public $value = '';

    /** @var string|null */
    public $type;

    /**
     * @param string|null $type
     */
    public function __construct(string $value, string $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public function is(array $type): bool
    {
        if ($this->type === null) {
            return false;
        }

        return in_array(strtolower($this->type), $type, true);
    }

    public function isStructured(): bool
    {
        return $this->type !== null;
    }

    public function isEmpty(): bool
    {
        return !$this->isStructured() && trim($this->value) === '';
    }
}
