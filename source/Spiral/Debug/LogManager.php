<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Debug;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Spiral\Core\Component;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

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
     * Container is needed to construct log handlers.
     *
     * @invisible
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
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

        //configuring logger!
        return new Logger($channel);
    }
}