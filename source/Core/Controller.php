<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Container\ArgumentException;
use Spiral\Debug\Traits\BenchmarkTrait;

abstract class Controller extends Component implements ControllerInterface
{
    /**
     * Benchmarking.
     */
    use BenchmarkTrait;

    /**
     * Action prefix will be assigned to every provided action. Useful when you need methods like
     * "new", "list" and etc.
     *
     * @var string
     */
    const ACTION_PREFIX = '';

    /**
     * Default action to run. This action will be performed if dispatcher didn't specified another
     * action to run.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Container interface used in callAction.
     *
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Last set of parameters passed to callAction method,
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Method executed before controller action beign called. Should return nothing to let controller
     * execute action itself. Any returned result will prevent action execution and will be returned
     * from callAction.
     *
     * @param \ReflectionMethod $method    Method reflection.
     * @param array             $arguments Method arguments.
     * @return mixed
     */
    protected function preAction(\ReflectionMethod $method, array $arguments)
    {
        return null;
    }

    /**
     * Method executed after controller action beign called. Original or altered result should be
     * returned.
     *
     * @param \ReflectionMethod $method    Method reflection.
     * @param array             $arguments Method arguments.
     * @param mixed             $result    Method result (plain output not included).
     * @return mixed
     */
    protected function postAction(\ReflectionMethod $method, array $arguments, $result)
    {
        return $result;
    }

    /**
     * Performing controller action. This method should either return response object or string, or
     * any other type supported by specified dispatcher. This method can be overwritten in child
     * controller to force some specific Response or modify output from every controller action.
     *
     * @param ContainerInterface $container
     * @param string             $action     Method name.
     * @param array              $parameters Set of parameters to populate controller method.
     * @return mixed
     * @throws ControllerException
     */
    public function callAction(ContainerInterface $container, $action = '', array $parameters = [])
    {
        //Action should include prefix and be always specified
        $action = static::ACTION_PREFIX . (!empty($action) ? $action : $this->defaultAction);

        if (!method_exists($this, $action))
        {
            throw new ControllerException(
                "No such controller action {$action}.",
                ControllerException::BAD_ACTION
            );
        }

        $reflection = new \ReflectionMethod($this, $action);

        if (
            $reflection->isStatic()
            || !$reflection->isPublic()
            || !$reflection->isUserDefined()
            || $reflection->getDeclaringClass()->getName() == __CLASS__
        )
        {
            throw new ControllerException(
                "Action {$action} can not be executed.",
                ControllerException::BAD_ACTION
            );
        }

        $this->container = $container;
        $this->parameters = $parameters;

        try
        {
            //Getting set of arguments should be sent to requested method
            $arguments = $this->container->resolveArguments($reflection, $parameters);
        }
        catch (ArgumentException $exception)
        {
            throw new ControllerException(
                "Missing/invalid parameter '{$exception->getParameter()->name}'.",
                ControllerException::BAD_ARGUMENT
            );
        }

        $action = $reflection->getName();
        if (($result = $this->preAction($reflection, $arguments)) !== null)
        {
            //Got filtered.
            return $result;
        }

        $this->benchmark($action);
        $result = $reflection->invokeArgs($this, $arguments);
        $this->benchmark($action);

        return $this->postAction($reflection, $arguments, $result);
    }

    /**
     * Magic access to core bindings.
     *
     * @param string $alias
     * @return mixed|null|object
     */
    public function __get($alias)
    {
        return $this->container->get($alias);
    }
}