<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Storage\StorageInterface;

class StorageSnapshot
{
    public function __construct(
        protected readonly string $bucket,
        protected readonly StorageInterface $storage,
        protected readonly Verbosity $verbosity,
        protected readonly ExceptionRendererInterface $renderer,
        protected readonly ?string $directory = null
    ) {
    }

    public function create(\Throwable $e): SnapshotInterface
    {
        $snapshot = new Snapshot($this->getID($e), $e);

        $this->saveSnapshot($snapshot);

        return $snapshot;
    }

    protected function saveSnapshot(SnapshotInterface $snapshot): void
    {
        $filename = $this->getFilename($snapshot, new \DateTime());

        $this->storage
            ->bucket($this->bucket)
            ->create($this->directory !== null ? $this->directory . DIRECTORY_SEPARATOR . $filename : $filename)
            ->write($this->renderer->render($snapshot->getException(), $this->verbosity));
    }

    /**
     * @throws \Exception
     */
    protected function getFilename(SnapshotInterface $snapshot, \DateTimeInterface $time): string
    {
        return \sprintf(
            '%s-%s.txt',
            $time->format('d.m.Y-Hi.s'),
            (new \ReflectionClass($snapshot->getException()))->getShortName()
        );
    }

    protected function getID(\Throwable $e): string
    {
        return \md5(\implode('|', [$e->getMessage(), $e->getFile(), $e->getLine()]));
    }
}
