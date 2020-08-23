<?php

/**
 * Spiral Framework.
 *
 * @author Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor\Fixture;

class SerializedStateObject
{
    public static function __set_state(array $data): self
    {
        return new self();
    }
}
