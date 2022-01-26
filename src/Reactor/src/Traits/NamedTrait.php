<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

/**
 * Provides ability to name declarations.
 */
trait NamedTrait
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * Attention, element name will be automatically classified.
     *
     *
     * @return $this|self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
