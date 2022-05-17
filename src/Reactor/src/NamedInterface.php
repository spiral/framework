<?php

declare(strict_types=1);

namespace Spiral\Reactor;

/**
 * Declaration with name.
 */
interface NamedInterface
{
    public function getName(): ?string;
}
