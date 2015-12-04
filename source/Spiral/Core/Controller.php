<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\HMVC\ControllerInterface;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Basic application controller class. Implements method injections and simplified access to
 * container bindings.
 */
abstract class Controller extends Service implements ControllerInterface
{
    /**
     * To benchmark action execution time.
     */
    use BenchmarkTrait, SaturateTrait;

    /**
     * Action method prefix value.
     *
     * @var string
     */
    const ACTION_PREFIX = '';

    /**
     * Action method postfix value.
     *
     * @var string
     */
    const ACTION_POSTFIX = 'Action';

    /**
     * Default action to run.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Needed to resolve method arguments.
     *
     * @invisible
     * @var ResolverInterface
     */
    protected $resolver = null;

    /**
     * @param ContainerInterface $container Sugared.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->resolver = $this->saturate($container, ContainerInterface::class);
        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function callAction($action = '', array $parameters = [])
    {
        //Action should include prefix and be always specified
        $action = static::ACTION_PREFIX
            . (!empty($action) ? $action : $this->defaultAction)
            . static::ACTION_POSTFIX;

        if (!method_exists($this, $action)) {
            throw new ControllerException(
                "No such action '{$action}'.", ControllerException::BAD_ACTION
            );
        }

        $reflection = new \ReflectionMethod($this, $action);

        if (!$this->isExecutable($reflection)) {
            //Need different exception code here
            throw new ControllerException(
                "Action '{$action}' can not be executed.",
                ControllerException::BAD_ACTION
            );
        }

        //Needed to be called via reflection
        $reflection->setAccessible(true);

        //Executing our action
        return $this->executeAction(
            $reflection,
            $this->resolveArguments($reflection, $parameters),
            $parameters
        );
    }

    /**
     * @param \ReflectionMethod $method
     * @param array $arguments
     * @param array $parameters
     * @return mixed
     */
    protected function executeAction(\ReflectionMethod $method, array $arguments, array $parameters)
    {
        $benchmark = $this->benchmark($method->getName());

        try {
            //Targeted controller method got called.
            return $method->invokeArgs($this, $arguments);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * Check if method is callable.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function isExecutable(\ReflectionMethod $method)
    {
        if ($method->isStatic() || !$method->isUserDefined()) {
            return false;
        }

        //Place to implement custom logic
        return true;
    }

    /**
     * Resolve controller method arguments.
     *
     * @param \ReflectionMethod $method
     * @param array $parameters
     * @return array
     */
    protected function resolveArguments(\ReflectionMethod $method, array $parameters)
    {
        try {
            //Getting set of arguments should be sent to requested method
            return $this->resolver->resolveArguments($method, $parameters);
        } catch (ArgumentException $exception) {
            throw new ControllerException(
                "Missing/invalid parameter '{$exception->getParameter()->name}'.",
                ControllerException::BAD_ARGUMENT
            );
        }
    }
}