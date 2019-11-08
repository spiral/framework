<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Snapshots;

use Psr\Log\LoggerInterface;
use Spiral\Exceptions\HandlerInterface;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\FilesInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FileSnapshooter implements SnapshotterInterface
{
    /** @var string */
    private $directory;

    /** @var int */
    private $maxFiles;

    /** @var int */
    private $verbosity;

    /** @var HandlerInterface */
    private $handler;

    /** @var FilesInterface */
    private $files;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * @param string               $directory
     * @param int                  $maxFiles
     * @param int                  $verbosity
     * @param HandlerInterface     $handler
     * @param FilesInterface       $files
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $directory,
        int $maxFiles,
        int $verbosity,
        HandlerInterface $handler,
        FilesInterface $files,
        LoggerInterface $logger = null
    ) {
        $this->directory = $directory;
        $this->maxFiles = $maxFiles;
        $this->verbosity = $verbosity;
        $this->handler = $handler;
        $this->files = $files;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function register(\Throwable $e): SnapshotInterface
    {
        $snapshot = new Snapshot($this->getID($e), $e);

        if ($this->logger !== null) {
            $this->logger->error($snapshot->getMessage());
        }

        $this->saveSnapshot($snapshot);
        $this->rotateSnapshots();

        return $snapshot;
    }

    /**
     * @param SnapshotInterface $snapshot
     * @throws \Exception
     */
    protected function saveSnapshot(SnapshotInterface $snapshot): void
    {
        $filename = $this->getFilename($snapshot, new \DateTime());

        $this->files->write(
            $filename,
            $this->handler->renderException($snapshot->getException(), $this->verbosity),
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
        $finder->in($this->directory)->sort(function (SplFileInfo $a, SplFileInfo $b) {
            return $b->getMTime() - $a->getMTime();
        });

        $count = 0;
        foreach ($finder as $file) {
            $count++;
            if ($count > $this->maxFiles) {
                try {
                    $this->files->delete($file->getRealPath());
                } catch (FilesException $e) {
                    // ignore
                }
            }
        }
    }

    /**
     * @param SnapshotInterface  $snapshot
     * @param \DateTimeInterface $time
     * @return string
     *
     * @throws \Exception
     */
    protected function getFilename(SnapshotInterface $snapshot, \DateTimeInterface $time): string
    {
        return sprintf(
            '%s/%s-%s.txt',
            $this->directory,
            $time->format('d.m.Y-Hi.s'),
            (new \ReflectionClass($snapshot->getException()))->getShortName()
        );
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    protected function getID(\Throwable $e): string
    {
        return md5(join('|', [$e->getMessage(), $e->getFile(), $e->getLine()]));
    }
}
