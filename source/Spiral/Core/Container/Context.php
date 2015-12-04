<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Container;

/**
 * Provided to method or constructor which declares such dependency.
 */
final class Context
{
    /**
     * @var \ReflectionFunctionAbstract
     */
    private $function = null;

    /**
     * @var \ReflectionParameter|null
     */
    private $parameter = null;

    /**
     * @param \ReflectionFunctionAbstract $function
     * @param \ReflectionParameter|null   $parameter
     */
    public function __construct(
        \ReflectionFunctionAbstract $function,
        \ReflectionParameter $parameter = null
    ) {
        $this->function = $function;
        $this->parameter = $parameter;
    }

    /**
     * Function or method or constructor.
     *
     * @return \ReflectionFunctionAbstract
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Can be empty.
     *
     * @return null|\ReflectionParameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}