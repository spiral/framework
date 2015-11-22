<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Bootloaders;

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
abstract class Bootloader implements BootloaderInterface
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
     * You don't need to bind classes which are declared with SINGLETON constant here, spiral will
     * resolve them as singleton automatically.
     *
     * @return array
     */
    protected $singletons = [];

    /**
     * Constructors are not allowed for bootloaders.
     */
    final public function __construct()
    {
    }

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
}