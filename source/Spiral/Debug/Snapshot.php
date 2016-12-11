<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

use Psr\Log\LoggerInterface;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewsInterface;

/**
 * Provides ability to render and store snapshot information on a disk.
 *
 * @see SnapshotConfig
 */
class Snapshot extends QuickSnapshot implements SnapshotInterface
{
    /**
     * @var SnapshotConfig
     */
    private $config = null;

    /**
     * Rendered backtrace view, can be used in to save into file, send by email or show to client.
     *
     * @var string
     */
    private $rendered = '';

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var ViewsInterface
     */
    protected $views = null;

    /**
     * @param \Throwable      $exception
     * @param LoggerInterface $logger Sugared.
     * @param SnapshotConfig  $config Sugared.
     * @param FilesInterface  $files  Sugared.
     * @param ViewsInterface  $views  Sugared.
     */
    public function __construct(
        $exception,
        LoggerInterface $logger,
        SnapshotConfig $config,
        FilesInterface $files,
        ViewsInterface $views
    ) {
        parent::__construct($exception, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        parent::report();

        if (!$this->config->reportingEnabled()) {
            //No need to record anything
            return;
        }

        $this->saveSnapshot();
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        if (!empty($this->rendered)) {
            return $this->rendered;
        }

        if (empty($this->config->viewName())) {
            return parent::render();
        }

        return $this->rendered = $this->views->render(
            $this->config->viewName(),
            ['exception' => $this->getException()]
        );
    }

    /**
     * Save snapshot information on hard-drive.
     */
    protected function saveSnapshot()
    {
        $filename = $this->config->snapshotFilename($this->getException(), time());

        $this->files->write($filename, $this->render(), FilesInterface::RUNTIME, true);

        //Rotating files
        $snapshots = $this->files->getFiles($this->config->reportingDirectory());
        if (count($snapshots) > $this->config->maxSnapshots()) {
            $this->performRotation($snapshots);
        }
    }

    /**
     * Clean old snapshots.
     *
     * @todo better implementation?
     *
     * @param array $snapshots
     */
    protected function performRotation(array $snapshots)
    {
        $oldest = '';
        $oldestTimestamp = PHP_INT_MAX;
        foreach ($snapshots as $snapshot) {
            $snapshotTimestamp = $this->files->time($snapshot);

            if ($snapshotTimestamp < $oldestTimestamp) {
                $oldestTimestamp = $snapshotTimestamp;
                $oldest = $snapshot;
            }
        }

        $this->files->delete($oldest);
    }
}