<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

/**
 * @internal
 */
trait NameAware
{
    public function getName(): ?string
    {
        return $this->element->getName();
    }
}
