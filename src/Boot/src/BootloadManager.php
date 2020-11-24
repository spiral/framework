<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Core\Container;

/**
 * Provides ability to bootload ServiceProviders.
 */
final class BootloadManager implements Container\SingletonInterface
{
    /* @var Container @internal */
    protected $container;

    /** @var array */
    private $classes = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get bootloaded classes.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Bootload set of classes. Support short and extended syntax with
     * bootload options (to be passed into boot method).
     *
     * [
     *    SimpleBootloader::class,
     *    CustomizedBootloader::class => ["option" => "value"]
     * ]
     *
     * @param array $classes
     *
     * @throws \Throwable
     */
    public function bootload(array $classes): void
    {
        $this->container->runScope(
            [self::class => $this],
            function () use ($classes): void {
                $this->boot($classes);
            }
        );
    }

    /**
     * Bootloader all given classes.
     *
     * @param array $classes
     *
     * @throws \Throwable
     */
    protected function boot(array $classes): void
    {
        foreach ($classes as $class => $options) {
            // default bootload syntax as simple array
            if (is_string($options)) {
                $class = $options;
                $options = [];
            }

            if (in_array($class, $this->classes)) {
                continue;
            }

            $this->classes[] = $class;
            $bootloader = $this->container->get($class);

            if (!$bootloader instanceof BootloaderInterface) {
                continue;
            }

            $this->initBootloader($bootloader, $options);
        }
    }

    /**
     * @param BootloaderInterface $bootloader
     * @param array               $options
     *
     * @throws \Throwable
     */
    protected function initBootloader(BootloaderInterface $bootloader, array $options = []): void
    {
        if ($bootloader instanceof DependedInterface) {
            $this->boot($bootloader->defineDependencies());
        }

        $this->initBindings($bootloader->defineBindings(), $bootloader->defineSingletons());

        if ((new \ReflectionClass($bootloader))->hasMethod('boot')) {
            $boot = new \ReflectionMethod($bootloader, 'boot');

            $args = $this->container->resolveArguments($boot, $options);
            if (!isset($args['boot'])) {
                $args['boot'] = $options;
            }

            $boot->invokeArgs($bootloader, \array_values($args));
        }
    }

    /**
     * Bind declared bindings.
     *
     * @param array $bindings
     * @param array $singletons
     */
    protected function initBindings(array $bindings, array $singletons): void
    {
        foreach ($bindings as $aliases => $resolver) {
            $this->container->bind($aliases, $resolver);
        }

        foreach ($singletons as $aliases => $resolver) {
            $this->container->bindSingleton($aliases, $resolver);
        }
    }
}
