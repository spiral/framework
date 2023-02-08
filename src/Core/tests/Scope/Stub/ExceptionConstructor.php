<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class ExceptionConstructor
{
    public const MESSAGE = 'Constructor test exception';
    public function __construct()
    {
        throw new \Exception(self::MESSAGE);
    }
}
