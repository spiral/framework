<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Container\SaturableInterface;
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
            $reflection->getName() == SaturableInterface::SATURATE_METHOD
            || $reflection->isStatic()
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

        //Executing our action
        return $this->executeAction($reflection, $arguments, $parameters);
    }

    /**
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @param array             $parameters
     * @return mixed
     */
    protected function executeAction(\ReflectionMethod $method, array $arguments, array $parameters)
    {
        $benchmark = $this->benchmark($action = $method->getName());

        //Executing target controller action using Container
        try {
            $result = $method->invokeArgs($this, $arguments);
        } finally {
            $this->benchmark($benchmark);
        }

        return $result;
    }
}