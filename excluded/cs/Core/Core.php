<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\Exceptions\FatalException;
use Spiral\Core\HMVC\ControllerInterface;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Core\Traits\SharedTrait;
use Spiral\Debug\SnapshotInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Http\HttpDispatcher;

/**
 * Spiral core responsible for application timezone, memory, represents spiral container (can be
 * overwritten with custom instance).
 *
 * Btw, you can design your architecture any way you want: MVC, MMVC, HMVC, ADR, anything which can
 * be invoked and/or routed. Technically you can even invent your own, application specific,
 * architecture.
 *
 * @property-read ContainerInterface $container Protected.
 * @todo move start method and dispatcher property into trait
 */
abstract class Core extends Component implements CoreInterface, DirectoriesInterface
{
    /**
     * Simplified access to container bindings.
     */
    use SharedTrait, BenchmarkTrait;

    /**
     * Not set until start method. Can be set manually in bootload.
     *
     * @whatif private
     * @var DispatcherInterface|null
     */
    protected $dispatcher = null;

    /**
     * {@inheritdoc}
     *
     * todo: add ability to register exception bridges (custom module exception => controller
     * exception)
     */
    public function callAction($controller, $action = '', array $parameters = [])
    {
        if (!class_exists($controller)) {
            throw new ControllerException(
                "No such controller '{$controller}' found.",
                ControllerException::NOT_FOUND
            );
        }

        $benchmark = $this->benchmark('callAction', $controller . '::' . ($action ?: '~default~'));

        $outerContainer = self::staticContainer($this->container);
        try {
            //Initiating controller with all required dependencies
            $controller = $this->container->make($controller);

            if (!$controller instanceof ControllerInterface) {
                throw new ControllerException(
                    "No such controller '{$controller}' found.",
                    ControllerException::NOT_FOUND
                );
            }

            return $controller->callAction($action, $parameters);
        } finally {
            $this->benchmark($benchmark);
            self::staticContainer($outerContainer);
        }
    }

    /**
     * Start application using custom or default dispatcher.
     *
     * @param DispatcherInterface $dispatcher Custom dispatcher.
     */
    public function start(DispatcherInterface $dispatcher = null)
    {
        //todo move dispatcher creation into core initialization method
        $this->dispatcher = !empty($dispatcher) ? $dispatcher : $this->createDispatcher();
        $this->dispatcher->start();
    }

    /**
     * Handle php shutdown and search for fatal errors.
     */
    public function handleShutdown()
    {
        if (!empty($error = error_get_last())) {
            $this->handleException(new FatalException(
                $error['message'], $error['type'], 0, $error['file'], $error['line']
            ));
        }
    }

    /**
     * Convert application error into exception.
     *
     * @param int    $code
     * @param string $message
     * @param string $filename
     * @param int    $line
     *
     * @throws \ErrorException
     */
    public function handleError($code, $message, $filename = '', $line = 0)
    {
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Handle exception using associated application dispatcher and snapshot class.
     *
     * @param \Throwable $exception
     */
    public function handleException(\Throwable $exception)
    {
        restore_error_handler();
        restore_exception_handler();

        if (empty($snapshot = $this->makeSnapshot($exception))) {
            //No action is required
            return;
        }

        //Let's allow snapshot to report about itself
        $snapshot->report();

        if (!empty($this->dispatcher)) {
            //Now dispatcher can handle snapshot it's own way
            $this->dispatcher->handleSnapshot($snapshot);
        } else {
            echo $snapshot->getException();
        }
    }

    /**
     * Create appropriate snapshot for given exception. By default SnapshotInterface binding will be
     * used.
     *
     * Method can return null, in this case exception will be ignored.
     *
     * @param \Throwable $exception
     *
     * @return SnapshotInterface|null
     */
    public function makeSnapshot(\Throwable $exception)
    {
        if (!$this->container->has(SnapshotInterface::class)) {
            return null;
        }

        return $this->container->make(SnapshotInterface::class, compact('exception'));
    }

    /**
     * Create default application dispatcher based on environment value.
     *
     * @return DispatcherInterface|ConsoleDispatcher|HttpDispatcher
     */
    protected function createDispatcher()
    {
        if (php_sapi_name() === 'cli') {
            return $this->container->make(ConsoleDispatcher::class);
        }

        return $this->container->make(HttpDispatcher::class);
    }
}