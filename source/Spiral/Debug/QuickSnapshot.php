<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Debug;

use Psr\Log\LoggerInterface;
use Spiral\Core\Component;
use Spiral\Support\ExceptionSupport;

class QuickSnapshot extends Component implements SnapshotInterface
{
    /**
     * @var \Exception
     */
    private $exception = null;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Snapshot constructor.
     *
     * @param \Throwable      $exception
     * @param LoggerInterface $logger
     */
    public function __construct($exception, LoggerInterface $logger = null)
    {
        $this->exception = $exception;
        $this->logger = $logger;
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
    public function formattedMessage()
    {
        return ExceptionSupport::createMessage($this->exception);
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        $this->logger->error($this->formattedMessage());
    }

    /**
     * {@inheritdoc}
     */
    public function describe()
    {
        return [
            'error'    => $this->formattedMessage(),
            'location' => [
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine()
            ],
            'trace'    => $this->exception->getTrace()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return "<pre>{$this->exception}</pre>";
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
}