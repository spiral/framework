<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Snapshots;

use Spiral\Exceptions\HandlerInterface;
use Spiral\Files\Exception\FilesException;
use Spiral\Files\FilesInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileSnapshotter implements SnapshotterInterface
{
    use LoggerTrait;

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

    /**
     * @param string           $directory
     * @param int              $maxFiles
     * @param int              $verbosity
     * @param HandlerInterface $handler
     * @param FilesInterface   $files
     */
    public function __construct(
        string $directory,
        int $maxFiles,
        int $verbosity,
        HandlerInterface $handler,
        FilesInterface $files
    ) {
        $this->directory = $directory;
        $this->maxFiles = $maxFiles;
        $this->verbosity = $verbosity;
        $this->handler = $handler;
        $this->files = $files;
    }

    /**
     * @inheritdoc
     */
    public function register(\Throwable $e): SnapshotInterface
    {
        $snapshot = new Snapshot($this->getID($e), $e);

        $this->getLogger()->error($snapshot->getMessage());

        $this->saveSnapshot($snapshot);
        $this->rotateSnapshots();

        return $snapshot;
    }

    /**
     * @param SnapshotInterface $snapshot
     */
    protected function saveSnapshot(SnapshotInterface $snapshot)
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
    protected function rotateSnapshots()
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
     */
    protected function getFilename(SnapshotInterface $snapshot, \DateTimeInterface $time): string
    {
        return sprintf(
            "%s/%s-%s.html",
            $this->directory,
            $time->format("d.m.Y-Hi.s"),
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