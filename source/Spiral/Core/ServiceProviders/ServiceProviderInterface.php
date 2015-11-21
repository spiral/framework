<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\ServiceProviders;

/**
 * Similar to laravel service provider, however allowed only to define bindings in a simple form so
 * they can be cached.
 *
 * @see BootableInterface
 */
interface ServiceProviderInterface
{
    /**
     * Bindings in string/array form, example:
     *
     * [
     *      'interface' => 'class',
     *      'class' => [self::class, 'createMethod']
     * ]
     *
     * @return array
     */
    public function defineBindings();

    /**
     * Singletons in string/array form, example:
     *
     * [
     *      'class' => 'otherClass',
     *      'class' => [self::class, 'createMethod']
     * ]
     *
     * @return array
     */
    public function defineSingletons();

    /**
     * Injectors in string/array form, example:
     *
     * [
     *      'class' => 'injector'
     * ]
     *
     * @return array
     */
    public function defineInjectors();
}