<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\ContainerException;
use Spiral\Core\Exceptions\Container\InjectionException;
use Spiral\Core\Exceptions\Container\InstanceException;

/**
 * Super simple auto-wiring container with auto SINGLETON and INJECTOR constants integration.
 * Compatible with Container Interop.
 *
 * Container does not support setter injections, private properties and etc. Normally will work with
 * classes only.
 *
 * @see InjectableInterface
 * @see SingletonInterface
 */
class Container extends Component implements ContainerInterface
{
    /**
     * IoC bindings. Binding one class or interface to another class and interface. :)
     *
     * @invisible
     * @var array
     */
    protected $bindings = [];

    /**
     * {@inheritdoc}
     */
    public function has($alias)
    {
        return isset($this->bindings[$alias]);
    }

    /**
     * {@inheritdoc}
     *
     * Context parameter will be passed to class injectors, which makes possible to use this method
     * as:
     * $this->container->get(DatabaseInterface::class, 'default');
     *
     * @param string|null $context Call context.
     */
    public function get($alias, $context = null)
    {
        //Direct bypass to construct, i might think about this option... or not.
        return $this->construct($alias, [], $context);
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null $context Related to parameter caused injection if any.
     */
    public function construct($class, $parameters = [], $context = null)
    {
        if (!isset($this->bindings[$class])) {
            //OK, we can create class by ourselves
            $instance = $this->createInstance($class, $parameters, $context, $reflector);

            /**
             * @var \ReflectionClass $reflector
             */
            if (
                $instance instanceof SingletonInterface
                && !empty($singleton = $reflector->getConstant('SINGLETON'))
            ) {
                //Component declared SINGLETON constant, binding as constant value and class name.
                $this->bindings[$singleton] = $instance;
            }

            return $instance;
        }

        if (is_object($binding = $this->bindings[$class])) {
            //Singleton
            return $binding;
        }

        if (is_string($binding)) {
            //Binding is pointing to something else
            return $this->construct($binding, $parameters, $context);
        }

        if (is_array($binding)) {
            if (is_string($binding[0])) {
                //Class name with singleton flag
                $instance = $this->construct($binding[0], $parameters, $context);
            } elseif ($binding[0] instanceof \Closure) {
                $reflection = new \ReflectionFunction($binding[0]);

                //Invoking Closure
                $instance = $reflection->invokeArgs(
                    $this->resolveArguments($reflection, $parameters)
                );
            } else {
                throw new ContainerException("Invalid binding.");
            }

            if ($binding[1]) {
                //Singleton
                $this->bindings[$class] = $instance;
            }

            return $instance;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveArguments(ContextFunction $reflection, array $parameters = [])
    {
        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            try {
                $class = $parameter->getClass();
            } catch (\ReflectionException $exception) {
                throw new ContainerException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );
            }

            if (empty($class)) {
                if (array_key_exists($name, $parameters)) {
                    //Scalar value supplied by user
                    $arguments[] = $parameters[$name];
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    //Or default value?
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                //Unable to resolve scalar argument value
                throw new ArgumentException($parameter, $reflection);
            }

            if (isset($parameters[$name]) && is_object($parameters[$name])) {
                //Supplied by user
                $arguments[] = $parameters[$name];
                continue;
            }

            try {
                //Trying to resolve dependency (contextually)
                $arguments[] = $this->construct($class->getName(), [], $parameter->getName());

                continue;
            } catch (InstanceException $exception) {
                if ($parameter->isDefaultValueAvailable()) {
                    //Let's try to use default value instead
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw $exception;
            }
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function bind($alias, $resolver)
    {
        if (is_array($resolver) || $resolver instanceof \Closure) {
            $this->bindings[$alias] = [$resolver, false];

            return $this;
        }

        $this->bindings[$alias] = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function bindSingleton($alias, $resolver)
    {
        if (is_object($resolver) && !$resolver instanceof \Closure) {
            $this->bindings[$alias] = $resolver;

            return $this;
        }

        $this->bindings[$alias] = [$resolver, true];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($alias, $resolver)
    {
        $payload = [$alias, null];
        if (isset($this->bindings[$alias])) {
            $payload[1] = $this->bindings[$alias];
        }

        $this->bind($alias, $resolver);

        return $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function restore($replacePayload)
    {
        list($alias, $resolver) = $replacePayload;

        unset($this->bindings[$alias]);

        if (!empty($resolver)) {
            //Restoring original value
            $this->bindings[$alias] = $replacePayload;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasInstance($alias)
    {
        if (!$this->has($alias)) {
            return false;
        }

        return is_object($this->bindings[$alias]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeBinding($alias)
    {
        unset($this->bindings[$alias]);
    }

    /**
     * Every declared Container binding. Must not be used in production code due container format is
     * vary.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Check if given class has associated injector.
     *
     * @param \ReflectionClass $reflection
     * @return bool
     */
    protected function hasInjector(\ReflectionClass $reflection)
    {
        //Custom logic can be applied
        return $reflection->isSubclassOf(InjectableInterface::class);
    }

    /**
     * Get injector associated with given class.
     *
     * @param \ReflectionClass $reflection
     * @return InjectorInterface
     */
    protected function getInjector(\ReflectionClass $reflection)
    {
        return $this->get($reflection->getConstant('INJECTOR'));
    }

    /**
     * Create instance of desired class.
     *
     * @param string           $class
     * @param array            $parameters     Constructor parameters.
     * @param string|null      $context
     * @param \ReflectionClass $reflection     Instance of reflection associated with class,
     *                                         reference.
     * @return object
     * @throws InstanceException
     */
    private function createInstance(
        $class,
        array $parameters,
        $context = null,
        \ReflectionClass &$reflection = null
    ) {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $exception) {
            throw new InstanceException(
                $exception->getMessage(), $exception->getCode(), $exception
            );
        }

        //We have to construct class using external injector
        if (empty($parameters) && $this->hasInjector($reflection)) {
            //Creating class using injector/factory
            $instance = $this->getInjector($reflection)->createInjection(
                $reflection,
                $context
            );

            if (!$reflection->isInstance($instance)) {
                throw new InjectionException("Invalid injector response.");
            }

            return $instance;
        }

        if (!$reflection->isInstantiable()) {
            throw new InstanceException("Class '{$class}' can not be constructed.");
        }

        if (!empty($constructor = $reflection->getConstructor())) {
            //Using constructor with resolved arguments
            $instance = $reflection->newInstanceArgs(
                $this->resolveArguments($constructor, $parameters)
            );
        } else {
            //No constructor specified
            $instance = $reflection->newInstance();
        }

        return $instance;
    }
}
