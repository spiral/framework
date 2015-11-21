<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Initializers;

/**
 * Provide ability to initiate set of container bindings using simple string form without closures.
 *
 * You can make any initializer automatically bootloadable by defining boot() method with
 * automatically resolved arguments.
 *
 * You can also declare Initializer classes as singletons while working using spiral container.
 *
 * This is almost the same as ServiceProvider.
 */
abstract class Initializer implements InitializerInterface
{
    /**
     * Not bootable by default.
     */
    const BOOT = false;

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
    protected $bindings = [];

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
    protected $singletons = [];

    /**
     * Injectors in string/array form, example:
     *
     * [
     *      'class' => 'injector'
     * ]
     *
     * @return array
     */
    protected $injectors = [];

    /**
     * {@inheritdoc}
     */
    public function defineBindings()
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function defineSingletons()
    {
        return $this->singletons;
    }

    /**
     * {@inheritdoc}
     */
    public function defineInjectors()
    {
        return $this->injectors;
    }
}