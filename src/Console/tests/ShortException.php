<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

final class ShortException extends \Exception
{
    public function __toString(): string
    {
        return 'exception';
    }
}
