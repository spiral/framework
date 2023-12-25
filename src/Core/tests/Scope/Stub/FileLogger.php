<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class FileLogger implements LoggerInterface
{
    public function getName(): string
    {
        return 'file';
    }
}
