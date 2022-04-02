<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

/**
 * Carries information about specific error.
 */
final class Snapshot implements SnapshotInterface
{
    public function __construct(
        private readonly string $id,
        private readonly \Throwable $exception
    ) {
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getMessage(): string
    {
        return \sprintf(
            '%s: %s in %s at line %s',
            $this->exception::class,
            $this->exception->getMessage(),
            $this->exception->getFile(),
            $this->exception->getLine()
        );
    }

    public function describe(): array
    {
        return [
            'error'    => $this->getMessage(),
            'location' => [
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ],
            'trace'    => $this->exception->getTrace(),
        ];
    }
}
