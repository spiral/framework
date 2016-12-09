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
}