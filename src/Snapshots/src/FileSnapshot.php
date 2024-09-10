<?php

declare(strict_types=1);

namespace Spiral\Snapshots;

use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\FilesInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileSnapshot
{
    public function __construct(
        private readonly string $directory,
        private readonly int $maxFiles,
        private readonly Verbosity $verbosity,
        private readonly ExceptionRendererInterface $renderer,
        private readonly FilesInterface $files
    ) {
    }

    public function create(\Throwable $e): SnapshotInterface
    {
        $snapshot = new Snapshot($this->getID($e), $e);

        $this->saveSnapshot($snapshot);
        $this->rotateSnapshots();

        return $snapshot;
    }

    protected function saveSnapshot(SnapshotInterface $snapshot): void
    {
        $filename = $this->getFilename($snapshot, new \DateTime());

        $this->files->write(
            $filename,
            $this->renderer->render($snapshot->getException(), $this->verbosity),
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Remove older snapshots.
     */
    protected function rotateSnapshots(): void
    {
        $finder = new Finder();
        $finder->in($this->directory)->sort(
            static fn (SplFileInfo $a, SplFileInfo $b) => $b->getMTime() - $a->getMTime()
        );

        $count = 0;
        foreach ($finder as $file) {
            $count++;
            if ($count > $this->maxFiles) {
                try {
                    $this->files->delete($file->getRealPath());
                } catch (FilesException) {
                    // ignore
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function getFilename(SnapshotInterface $snapshot, \DateTimeInterface $time): string
    {
        return \sprintf(
            '%s/%s-%s.txt',
            $this->directory,
            $time->format('d.m.Y-Hi.s'),
            (new \ReflectionClass($snapshot->getException()))->getShortName()
        );
    }

    protected function getID(\Throwable $e): string
    {
        return \md5(\implode('|', [$e->getMessage(), $e->getFile(), $e->getLine()]));
    }
}
