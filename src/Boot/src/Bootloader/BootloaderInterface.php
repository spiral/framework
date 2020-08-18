<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * Similar to laravel service provider, however allowed only to define bindings in a simple form so
 * they can be cached.
 *
 * To make class bootable (using method boot() with method injections) declare constant BOOT = true;
 */
interface BootloaderInterface
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
    public function defineBindings(): array;

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
    public function defineSingletons(): array;
}
