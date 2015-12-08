<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Spiral\Core\Component;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Debug\Configs\SnapshotingConfig;
use Spiral\Debug\Traits\LoggerTrait;
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
     * Additional constructor arguments.
     */
    use SaturateTrait;

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
     * @throws SugarException
     */
    public function __construct(
        $exception,
        LoggerInterface $logger = null,
        SnapshotConfig $config = null,
        FilesInterface $files = null,
        ViewsInterface $views = null
    ) {
        /**
         * All this properties can be automatically populated using shared contaner.
         */
        $this->config = $this->saturate($config, SnapshotingConfig::class);
        $this->files = $this->saturate($files, FilesInterface::class);
        $this->views = $this->saturate($views, ViewsInterface::class);

        parent::__construct($exception, $this->saturate($logger, LoggerInterface::class));
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
    public function render()
    {
        if (!empty($this->rendered)) {
            return $this->rendered;
        }

        if (empty($this->config->viewName())) {
            return parent::render();
        }

        return $this->rendered = $this->views->render($this->config->viewName(), [
            'exception' => $this->exception()
        ]);
    }

    /**
     * Save snapshot information on hard-drive.
     */
    protected function saveSnapshot()
    {
        $filename = $this->config->snapshotFilename($this->exception(), time());
        $this->files->write($filename, $this->render(), FilesInterface::RUNTIME, true);

        $snapshots = $this->files->getFiles($this->config->reportingDirectory());
        if (count($snapshots) > $this->config->maxSnapshots()) {
            $this->performRotation($snapshots);
        }
    }

    /**
     * Clean old snapshots.
     *
     * @todo Possibly need better implementation.
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