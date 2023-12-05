<?php

declare(strict_types=1);

namespace Spiral\Session\Handler;

/**
 * Blackhole.
 */
final class NullHandler implements \SessionHandlerInterface
{
    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function gc(int $maxlifetime): int
    {
        return $maxlifetime;
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function open(string $path, string $id): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        return '';
    }

    public function write(string $id, string $data): bool
    {
        return true;
    }
}
