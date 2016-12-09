<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Debug;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Debug\Configs\LogsConfig;

/**
 * Manages creation of loggers and log handlers. Spiral implementation is build at top of Monolog
 * extension. Which is super nice :)
 */
class LogManager extends Component implements SingletonInterface, LogsInterface
{
    /**
     * Common logger stream (in case where no channel is provided). Usually this channel is used to
     * store debug messages.
     */
    const DEBUG_CHANNEL = 'debug';

    /**
     * Common/shared logger.
     *
     * @var LoggerInterface
     */
    private $sharedLogger;

    /**
     * Handler to be added to every created logger (already exists loggers would not be affected).
     *
     * @var HandlerInterface
     */
    private $sharedHandler;

    /**
     * @var LogsConfig
     */
    protected $config;

    /**
     * Container is needed to construct log handlers.
     *
     * @invisible
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @param LogsConfig       $config
     * @param FactoryInterface $factory
     */
    public function __construct(LogsConfig $config, FactoryInterface $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(string $channel = null): LoggerInterface
    {
        /*
         * In some cases code needs some logger without specifying anything like channel, let's use
         * shared debug logger in this case.
         */
        if (empty($channel)) {
            if (!empty($this->sharedLogger)) {
                return $this->sharedLogger;
            }

            //When no channel is provided we are going to use same shared logger
            return $this->sharedLogger = $this->getLogger(self::DEBUG_CHANNEL);
        }

        return new Logger(
            $channel,
            $this->createHandlers($channel),
            $this->createProcessors($channel)
        );
    }

    /**
     * Set instance of shared HandlerInterface, such handler will be passed to every created log.
     * To remove existed handler set it argument as null.
     *
     * @param HandlerInterface $handler
     *
     * @return HandlerInterface|null Returns previously set handler.
     */
    public function shareHandler(HandlerInterface $handler = null)
    {
        $previous = $this->sharedHandler;
        $this->sharedHandler = $handler;

        return $previous;
    }

    /**
     * Get list of channel specific handlers.
     *
     * @param string $channel
     *
     * @return array
     */
    protected function createHandlers(string $channel): array
    {
        $result = [];

        if (!empty($this->sharedHandler)) {
            $result[] = $this->sharedHandler;
        }

        if ($this->config->hasHandlers($channel)) {
            //Creating handlers
            foreach ($this->config->logHandlers($channel) as $handler) {
                $result[] = $this->createHandler($handler);
            }
        }

        return $result;
    }

    /**
     * Get list of channel specific log processors.
     *
     * @param string $channel
     *
     * @return callable[]
     */
    protected function createProcessors(string $channel): array
    {
        //Not implemented for now
        return [
            new PsrLogMessageProcessor()
        ];
    }

    /**
     * Create instance of handler based on a config definition.
     *
     * @param array $handler
     *
     * @return HandlerInterface
     */
    protected function createHandler(array $handler): HandlerInterface
    {
        /**
         * @var HandlerInterface $instance
         */
        $instance = $this->factory->make($handler['handler'], $handler['options']);

        if (!empty($handler['format'])) {
            //Shortcut
            $instance->setFormatter(new LineFormatter($handler['format']));
        }

        return $instance;
    }
}