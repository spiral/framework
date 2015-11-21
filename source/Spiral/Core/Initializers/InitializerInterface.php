<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Initializers;

/**
 * Similar to laravel service provider, however allowed only to define bindings in a simple form so
 * they can be cached.
 *
 * To make class bootable (using method boot() with method injections) declare constant BOOT = true;
 */
interface InitializerInterface
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