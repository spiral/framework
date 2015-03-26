<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Component;

use Spiral\Core\Container;
use Spiral\Core\CoreException;

trait SingletonTrait
{
    /**
     * Return component alias binding.
     *
     * @return string
     */
    public static function getAlias()
    {
        return static::SINGLETON;
    }

    /**
     * Return singleton component instance, such method can be used only if "SINGLETON" constant
     * declared and not empty. Class will be automatically created using IoC. Another class instance
     * or realization can be binded under componentAlias, in this case this method return that object.
     *
     * @return static
     * @throws CoreException
     */
    public static function getInstance()
    {
        if (!$alias = static::SINGLETON)
        {
            throw new CoreException(
                "Unable to get instance of '" . get_called_class() . "', no 'SINGLETON' constant."
            );
        }

        return Container::get(static::SINGLETON);
    }
}