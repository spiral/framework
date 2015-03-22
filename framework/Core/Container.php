<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use ReflectionParameter;
use Spiral\Core\Container\ContainerException;

class Container extends Component
{
    /**
     * IoC bindings. Binding can include interface - class aliases, closures, singleton closures
     * and already constructed components stored as instances. Binding can be added using
     * Container::bind() or Container::bindSingleton() methods, every existed binding can be defined
     * or redefined at any moment of application flow.
     *
     * Instance or class name can be also binded to alias, this technique used for all spiral core
     * components and can simplify development. Spiral additionally provides way to create DI without
     * binding, it can be done by using real class or model name, or via ControllableInjection interface.
     *
     * @var array
     */
    protected static $bindings = array();

    /**
     * Resolve class instance using IoC container. Class can be requested using it's own name, alias
     * binding, singleton binding, closure function, closure function with singleton resolution, or
     * via InjectableInterface interface. To add binding use Container::bind() or Container::bindSingleton()
     * methods.
     *
     * This method widely used inside spiral core to resolve adapters, handlers and databases.
     *
     * @param string              $alias            Class/interface name or binded alias should be
     *                                              resolved to instance.
     * @param array               $parameters       Parameters to be mapped to class constructor or
     *                                              forwarded to closure.
     * @param ReflectionParameter $contextParameter Parameter were used to declare DI.
     * @param bool                $ignoreII         If true, core will ignore InjectableInterface and
     *                                              resolve class as usual.
     * @param string              $requester        Class name of injection requester.
     * @return mixed|null|object
     * @throws CoreException
     * @throws ContainerException
     */
    public static function get(
        $alias,
        $parameters = array(),
        ReflectionParameter $contextParameter = null,
        $ignoreII = false,
        $requester = null
    )
    {
        if (!isset(self::$bindings[$alias]))
        {
            $reflector = new \ReflectionClass($alias);
            if (!$ignoreII && $injectionManager = $reflector->getConstant('INJECTION_MANAGER'))
            {
                //Apparently checking constant is faster than checking interface
                return call_user_func(
                    array($injectionManager, 'resolveInjection'),
                    $reflector,
                    $contextParameter
                );
            }
            elseif ($reflector->isInstantiable())
            {
                if ($constructor = $reflector->getConstructor())
                {
                    $instance = $reflector->newInstanceArgs(
                        self::resolveArguments($constructor, $parameters)
                    );
                }
                else
                {
                    $instance = $reflector->newInstance();
                }

                //Component declared SINGLETON constant, binding as constant value and class name.
                if ($singleton = $reflector->getConstant('SINGLETON'))
                {
                    self::$bindings[$reflector->getName()] = self::$bindings[$singleton] = $instance;
                }

                return $instance;
            }

            throw new CoreException("Class '{$alias}' can not be constructed.", 7);
        }

        if (is_object($binding = self::$bindings[$alias]))
        {
            return $binding;
        }

        if (is_string($binding))
        {
            $instance = self::get($binding, $parameters, $contextParameter, $ignoreII);
            if ($instance instanceof Component && $instance::SINGLETON)
            {
                //To prevent double binding
                self::$bindings[$binding] = self::$bindings[get_class($instance)] = $instance;
            }

            return $instance;
        }

        if (is_array($binding))
        {
            if (is_string($binding[0]))
            {
                $instance = self::get(
                    $binding[0],
                    $parameters,
                    $contextParameter,
                    $ignoreII, $requester
                );
            }
            else
            {
                $instance = call_user_func_array($binding[0], $parameters);
            }

            if ($binding[1])
            {
                //Singleton
                self::$bindings[$alias] = $instance;
            }

            return $instance;
        }

        return null;
    }

    /**
     * Helper method to resolve constructor or function arguments, build required DI using IoC
     * container and mix with pre-defined set of named parameters.
     *
     * @param \ReflectionMethod $reflection    Method or constructor should be filled with DI.
     * @param array             $parameters    Outside parameters used in priority to DI. Named list.
     * @param bool              $userArguments If true no exception will be raised while some argument
     *                                         (not DI) can not be resolved. This parameter used to
     *                                         pass error to controller.
     * @return array
     * @throws ContainerException
     */
    public static function resolveArguments(
        \ReflectionMethod $reflection,
        array $parameters = array(),
        $userArguments = false
    )
    {
        try
        {
            $arguments = array();
            foreach ($reflection->getParameters() as $parameter)
            {
                if (array_key_exists($parameter->getName(), $parameters))
                {
                    $parameterValue = $parameters[$parameter->getName()];

                    if (!$userArguments || !$parameter->getClass() || is_object($parameterValue))
                    {
                        //Provided directly
                        $arguments[] = $parameterValue;
                        continue;
                    }
                }

                if ($parameter->getClass())
                {
                    try
                    {
                        $arguments[] = self::get(
                            $parameter->getClass()->getName(),
                            array(),
                            $parameter,
                            false,
                            $reflection->class
                        );

                        continue;
                    }
                    catch (CoreException $exception)
                    {
                        if (!$parameter->isDefaultValueAvailable() || $exception->getCode() != 7)
                        {
                            throw $exception;
                        }
                    }
                }

                if ($parameter->isDefaultValueAvailable())
                {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                if (!$userArguments)
                {
                    throw new CoreException(
                        "Unable to resolve '{$parameter->getName()}' argument in '{$reflection->class}'."
                    );
                }
            }
        }
        catch (\Exception $exception)
        {
            throw new ContainerException($exception, $reflection);
        }

        return $arguments;
    }

    /**
     * IoC binding can create a link between specified alias and method to resolve that alias, resolver
     * can be either class instance (that instance will be resolved as singleton), callback or string
     * alias. String aliases can be used to rewrite core classes with custom realization, or specify
     * what interface child should be used.
     *
     * @param string                 $alias  Alias where singleton will be attached to.
     * @param string|object|callable Closure to resolve class instance, class instance or class name.
     */
    public static function bind($alias, $resolver)
    {
        if (is_array($resolver) || $resolver instanceof \Closure)
        {
            self::$bindings[$alias] = array($resolver, false);

            return;
        }

        self::$bindings[$alias] = $resolver;
    }

    /**
     * Bind closure or class name which will be performed only once, after first call class instance
     * will be attached to specified alias and will be returned directly without future invoking.
     *
     * @param string   $alias    Alias where singleton will be attached to.
     * @param callable $resolver Closure to resolve class instance.
     */
    public static function bindSingleton($alias, $resolver)
    {
        self::$bindings[$alias] = array($resolver, true);
    }

    /**
     * Check if desired alias or class name binded in IoC container. You can bind new alias using
     * Container::bind(), Container::bindSingleton().
     *
     * @param string $alias
     * @return bool
     */
    public static function hasBinding($alias)
    {
        return isset(self::$bindings[$alias]);
    }

    /**
     * Return binding resolver in original form (without processing it to instance).
     *
     * @param string $alias
     * @return mixed
     */
    public static function getBinding($alias)
    {
        return isset(self::$bindings[$alias]) ? self::$bindings[$alias] : null;
    }

    /**
     * Removed existed binding.
     *
     * @param string $alias
     */
    public static function removeBinding($alias)
    {
        unset(self::$bindings[$alias]);
    }

    /**
     * Return all available bindings and binded components.
     *
     * @return array
     */
    public static function getBindings()
    {
        return self::$bindings;
    }
}