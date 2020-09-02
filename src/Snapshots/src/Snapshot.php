<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Snapshots;

/**
 * Carries information about specific error.
 */
final class Snapshot implements SnapshotInterface
{
    /** @var string */
    private $id;

    /** @var \Throwable */
    private $exception;

    /**
     * @param string     $id
     * @param \Throwable $exception
     */
    public function __construct(string $id, \Throwable $exception)
    {
        $this->id = $id;
        $this->exception = $exception;
    }

    /**
     * @inheritdoc
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @inheritdoc
     */
    public function getMessage(): string
    {
        return sprintf(
            '%s: %s in %s at line %s',
            get_class($this->exception),
            $this->exception->getMessage(),
            $this->exception->getFile(),
            $this->exception->getLine()
        );
    }

    /**
     * @inheritdoc
     */
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
