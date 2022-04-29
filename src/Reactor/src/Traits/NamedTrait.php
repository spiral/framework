<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

/**
 * Provides ability to name declarations.
 */
trait NamedTrait
{
    private string $name = '';

    /**
     * Attention, element name will be automatically classified.
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
