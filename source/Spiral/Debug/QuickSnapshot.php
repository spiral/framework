<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class QuickSnapshot implements SnapshotInterface
{
    /**
     * Additional constructor arguments.
     */
    use LoggerTrait;

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
     * Snapshot constructor.
     *
     * @param \Throwable      $exception
     * @param LoggerInterface $logger
     */
    public function __construct(
        $exception,
        LoggerInterface $logger = null

    ) {
        $this->exception = $exception;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return (new \ReflectionObject($this->exception))->getShortName();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return ExceptionSupport::createMessage($this->exception);
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
    public function report()
    {
        $this->logger()->error(ExceptionSupport::createMessage($this->exception));

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
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine()
            ],
            //todo: needed?
            'trace'    => $this->exception->getTrace()
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
            'exception' => $this->exception
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