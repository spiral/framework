<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

abstract class AbstractEntity
{
    /** @var string */
    public $name;

    /** @var int */
    public $sequence = 0;

    /**
     * AbstractEntity constructor.
     */
    protected function __construct()
    {
    }

    public function fullName(): string
    {
        $name = $this->name;
        if ($this->sequence > 0) {
            $name .= $this->sequence;
        }

        return $name;
    }
}
