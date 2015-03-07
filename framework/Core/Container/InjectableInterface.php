<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Container;

interface InjectableInterface
{
    /**
     * InjectableInterface declares to spiral Container that requested interface or class should not be resolved using
     * default mechanism. Following interface does not require any methods, however class or other interface which inherits
     * ControllableInjection should declare constant named "INJECTION_MANAGER" with name of class responsible for resolving that
     * injection.
     *
     * InjectionFactory will receive requested class or interface reflection and reflection linked to parameter in
     * constructor or method used to declare injection.
     */
}