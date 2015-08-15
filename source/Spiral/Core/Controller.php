<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\ControllerException;
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
    use BenchmarkTrait;

    /**
     * Action method prefix value.
     *
     * @var string
     */
    const ACTION_PREFIX = '';

    /**
     * Default action to run.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * {@inheritdoc}
     */
    public function callAction($action = '', array $parameters = [])
    {
        //Action should include prefix and be always specified
        $action = static::ACTION_PREFIX . (!empty($action) ? $action : $this->defaultAction);

        if (!method_exists($this, $action)) {
            throw new ControllerException(
                "No such action '{$action}'.",
                ControllerException::BAD_ACTION
            );
        }

        $reflection = new \ReflectionMethod($this, $action);

        if (
            $reflection->isStatic()
            || !$reflection->isPublic()
            || !$reflection->isUserDefined()
            || $reflection->getDeclaringClass()->getName() == __CLASS__
        ) {
            throw new ControllerException(
                "Action '{$action}' can not be executed.",
                ControllerException::BAD_ACTION
            );
        }

        try {
            //Getting set of arguments should be sent to requested method
            $arguments = $this->container->resolveArguments($reflection, $parameters);
        } catch (ArgumentException $exception) {
            throw new ControllerException(
                "Missing/invalid parameter '{$exception->getParameter()->name}'.",
                ControllerException::BAD_ARGUMENT
            );
        }

        if (($result = $this->preAction($reflection, $arguments, $parameters)) !== null) {
            //Got filtered.
            return $result;
        }

        $this->benchmark($action = $reflection->getName());
        $result = $reflection->invokeArgs($this, $arguments);
        $this->benchmark($action);

        return $this->postAction($result, $reflection, $arguments, $parameters);
    }

    /**
     * Executed before action call, can return non empty value to be sent to client.
     *
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @param array             $parameters
     * @return mixed
     */
    protected function preAction(
        \ReflectionMethod $method,
        array $arguments,
        array $parameters
    ) {
        return null;
    }

    /**
     * Executed after action with action result to be filtered. Must return result in normal flow.
     *
     * @param mixed             $result
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @param array             $parameters
     * @return mixed
     */
    protected function postAction(
        $result,
        \ReflectionMethod $method,
        array $arguments,
        array $parameters
    ) {
        return $result;
    }
}