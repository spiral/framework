<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Debug;

use Psr\Log\LoggerInterface;
use Spiral\Support\ExceptionHelper;

/**
 * Wraps exception message and automatically stores this message into associated logger.
 */
class QuickSnapshot implements SnapshotInterface
{
    /**
     * @var \Throwable
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
    public function __construct(\Throwable $exception, LoggerInterface $logger = null)
    {
        $this->exception = $exception;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return ExceptionHelper::createMessage($this->exception);
    }

    /**
     * {@inheritdoc}
     */
    public function report()
    {
        if (!empty($this->logger)) {
            $this->logger->error($this->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function describe(): array
    {
        return [
            'error'    => $this->getMessage(),
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
    public function render(): string
    {
        return "<pre>{$this->exception}</pre>";
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (php_sapi_name() == 'cli') {
            return (string)$this->exception;
        }

        return $this->render();
    }
}