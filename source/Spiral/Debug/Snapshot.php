<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Spiral\Core\Component;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewsInterface;

/**
 * Spiral implementation of SnapshotInterface with ability to render exception explanation using
 * ViewsInterface.
 *
 * TODO: REWRITE OR REMOVE THAT? STILL THINKING
 */
class Snapshot extends Component implements SnapshotInterface, LoggerAwareInterface
{
    /**
     * Additional constructor arguments.
     */
    use SaturateTrait, LoggerTrait;

    /**
     * Message format.
     */
    const MESSAGE = "{exception}: {message} in {file} at line {line}";

    /**
     * Part of debug configuration.
     */
    const CONFIG = 'snapshots';

    /**
     * @var \Exception
     */
    private $exception = null;

    /**
     * Rendered backtrace view, can be used in to save into file, send by email or show to client.
     *
     * @var string
     */
    private $rendered = '';

    /**
     * @var SnapshotConfig
     */
    protected $config = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var ViewsInterface
     */
    protected $views = null;

    /**
     * Snapshot constructor.
     *
     * @param Exception       $exception
     * @param LoggerInterface $logger
     * @param SnapshotConfig  $config
     * @param FilesInterface  $files
     * @param ViewsInterface  $views
     * @throws SugarException
     */
    public function __construct(
        $exception,
        LoggerInterface $logger,
        SnapshotConfig $config,
        FilesInterface $files,
        ViewsInterface $views
    ) {
        $this->exception = $exception;
        $this->logger = $logger;

        $this->config = $this->saturate($config, SnapshotConfig::class);

        //We can use global container as fallback if no default values were provided
        $this->files = $this->saturate($files, FilesInterface::class);
        $this->views = $this->saturate($views, ViewsInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return (new \ReflectionObject($this->exception))->getShortName();
    }

    /**
     * {@inheritdoc}
     */
    public function exception()
    {
        return $this->exception;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return get_class($this->exception);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->exception->getFile();
    }

    /**
     * {@inheritdoc}
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return \Spiral\interpolate(static::MESSAGE, [
            'exception' => $this->getClass(),
            'message'   => $this->exception->getMessage(),
            'file'      => $this->getFile(),
            'line'      => $this->getLine()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        $this->logger()->error($this->getMessage());

        if (!$this->config->reportingEnabled()) {
            //No need to record anything
            return;
        }

        //Writing to hard drive
        $this->files->write(
            $this->config->snapshotFilename($this->getName(), time()),
            $this->render(),
            FilesInterface::RUNTIME,
            true
        );

        $snapshots = $this->files->getFiles($this->config->reportingDirectory());
        if (count($snapshots) > $this->config->maxSnapshots()) {
            $this->dropOldest($snapshots);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function describe()
    {
        return [
            'error'    => $this->getMessage(),
            'location' => [
                'file' => $this->getFile(),
                'line' => $this->getLine()
            ],
            'trace'    => $this->getTrace()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        if (!empty($this->rendered)) {
            return $this->rendered;
        }

        return $this->rendered = $this->views->render($this->config->viewName(), [
            'snapshot' => $this
        ]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (php_sapi_name() == 'cli') {
            return (string)$this->exception;
        }

        return $this->render();
    }

    /**
     * Clean old snapshots.
     *
     * @param array $snapshots
     */
    private function dropOldest(array $snapshots)
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