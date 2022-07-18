<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

class ShortException extends \Exception
{
    public function __toString()
    {
        return 'exception';
    }
}
