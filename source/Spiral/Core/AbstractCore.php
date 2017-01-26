<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\HMVC\ControllerInterface;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Provides ability to call controllers in IoC scope.
 */
abstract class AbstractCore extends Component implements CoreInterface
{
    use BenchmarkTrait;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function callAction(
        string $controller,
        string $action = null,
        array $parameters = [],
        array $scope = []
    ) {
        if (!class_exists($controller)) {
            throw new ControllerException(
                "No such controller '{$controller}' found",
                ControllerException::NOT_FOUND
            );
        }

        $benchmark = $this->benchmark('callAction', $controller . '::' . ($action ?? '~default~'));

        //Making sure that all static functionality works well
        $iocScope = self::staticContainer($this->container);

        //Working with container scope
        foreach ($scope as $alias => &$target) {
            $target = $this->container->replace($alias, $target);
            unset($target);
        }

        try {
            //Getting instance of controller
            $instance = $this->container->get($controller);

            if (!$instance instanceof ControllerInterface) {
                throw new ControllerException(
                    "No such controller '{$controller}' found",
                    ControllerException::NOT_FOUND
                );
            }

            return $instance->callAction($action, $parameters);
        } finally {
            $this->benchmark($benchmark);

            //Restoring container scope
            foreach (array_reverse($scope) as $payload) {
                $this->container->restore($payload);
            }

            //Restoring shared container to it's original state
            self::staticContainer($iocScope);
        }
    }
}