<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Debug;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Exception;
use Spiral\Core\CoreInterface;
use Spiral\Core\Dispatcher\ClientException;

class Debugger extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait,
        Component\ConfigurableTrait,
        Component\LoggerTrait,
        Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Benchmarking. You can use Debugger::benchmark('record') to start and stop profiling for some
     * operations. Multiple spiral components already have benchmarking mounted, however benchmarking
     * is disabled by default and be enabled by Debugger::benchmarking() method. Benchmarks can
     * retrieved using Debugger::getBenchmarks() method.
     *
     * @var array
     */
    protected static $benchmarks = array();

    /**
     * Constructing debug component. Debug is one of primary spiral component and will be available
     * for use in any environment and any application point. This is first initiated component in
     * application.
     *
     * @param ConfiguratorInterface $configurator
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->config = $configurator->getConfig('debug');
    }

    /**
     * Get list of file handlers associated with specified container.
     *
     * @param string $container
     * @return array
     */
    public function getLogHandlers($container)
    {
        return isset($this->config['loggers']['containers'][$container])
            ? $this->config['loggers']['containers'][$container]
            : array();
    }

    /**
     * Benchmark method used to determinate how long time and how much memory was used to perform
     * some specified piece of code. Method should be used twice, before and after code needs to be
     * profile, first call will return true, second one will return time in seconds took to perform
     * code between benchmark method calls. If Debugger::$benchmarking enabled - result will be
     * additionally logged in Debugger::$benchmarks array and can be retrieved using
     * Debugger::getBenchmarks() for future analysis.
     *
     * Example:
     * Debugger::benchmark('parseURL', 'google.com');
     * ...
     * echo Debugger::benchmark('parseURL', 'google.com');
     *
     * @param string $record Record name.
     * @return bool|float
     */
    public static function benchmark($record)
    {
        if (func_num_args() > 1)
        {
            $record = join('|', func_get_args());
        }

        if (!isset(self::$benchmarks[$record]))
        {
            self::$benchmarks[$record] = array(
                microtime(true),
                memory_get_usage()
            );

            return true;
        }

        self::$benchmarks[$record][] = microtime(true);
        self::$benchmarks[$record][] = memory_get_usage();

        return self::$benchmarks[$record][2] - self::$benchmarks[$record][0];
    }

    /**
     * Retrieve all active and finished benchmark records, this method will return finished records
     * only if Debugger::$benchmarking is true, in opposite case all finished records will be erased
     * right after completion.
     *
     * @return array|null
     */
    public static function getBenchmarks()
    {
        return self::$benchmarks;
    }

    /**
     * Will convert Exception to ExceptionResponse object which can be passed further to dispatcher
     * and handled by environment logic. Additionally error message will be recorded in "error" debug
     * container.
     *
     * ExceptionResponse will contain full exception explanation and rendered snapshot which can be
     * recorded as html file for future usage.
     *
     * @param Exception $exception
     * @param bool      $logException If true (default), message to error container will be added.
     * @return Snapshot
     */
    public function handleException(Exception $exception, $logException = true)
    {
        $snapshot = Snapshot::make(array(
            'exception' => $exception,
            'view'      => $this->config['backtrace']['view'],
            'config'    => $this->config['backtrace']['snapshots']
        ));

        if ($exception instanceof ClientException)
        {
            //No logging for ClientExceptions
            return $snapshot;
        }

        //Letting subscribers know...
        $this->event('snapshot', $snapshot);

        //Error message should be added to log only for non http exceptions
        if ($logException)
        {
            self::logger()->error($snapshot->getMessage());
        }

        return $snapshot;
    }
}