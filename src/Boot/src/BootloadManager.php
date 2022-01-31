<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Closure;
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

    /** @var array<string> */
    private $classes = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get bootloaded classes.
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
     * @param array<int,string>|array<string,array<string,mixed>> $classes
     * @param array<Closure> $bootingCallbacks
     * @param array<Closure> $bootedCallbacks
     * @throws \Throwable
     */
    public function bootload(array $classes, array $bootingCallbacks = [], array $bootedCallbacks = []): void
    {
        $this->container->runScope(
            [self::class => $this],
            function () use ($classes, $bootingCallbacks, $bootedCallbacks): void {
                $this->boot($classes, $bootingCallbacks, $bootedCallbacks);
            }
        );
    }

    /**
     * Bootloader all given classes.
     *
     * @throws \Throwable
     */
    protected function boot(array $classes, array $bootingCallbacks, array $bootedCallbacks): void
    {
        $bootloaders = [];

        foreach ($classes as $class => $options) {
            // default bootload syntax as simple array
            if (\is_string($options)) {
                $class = $options;
                $options = [];
            }

            // Replace class aliases with source classes
            try {
                $class = (new \ReflectionClass($class))->getName();
            } catch (\ReflectionException $e) {
                throw new \Spiral\Boot\Exception\ClassNotFoundException();
            }

            if (\in_array($class, $this->classes, true)) {
                continue;
            }

            $this->classes[] = $class;
            $bootloader = $this->container->get($class);

            if (! $bootloader instanceof BootloaderInterface) {
                continue;
            }

            $this->initBootloader($bootloader, $bootingCallbacks, $bootedCallbacks);
            $bootloaders[] = \compact('bootloader', 'options');

            $this->invokeBootloader($bootloader, 'register', $options);
        }

        $this->fireCallbacks($bootingCallbacks);
        foreach ($bootloaders as $data) {
            $bootloader = $data['bootloader'];
            $options = $data['options'];
            $this->invokeBootloader($bootloader, 'boot', $options);
        }
        $this->fireCallbacks($bootedCallbacks);

        unset($bootloaders);
    }

    /**
     * @throws \Throwable
     */
    protected function initBootloader(
        BootloaderInterface $bootloader,
        array $bootingCallbacks,
        array $bootedCallbacks
    ): void {
        if ($bootloader instanceof DependedInterface) {
            $this->boot($bootloader->defineDependencies(), $bootingCallbacks, $bootedCallbacks);
        }

        $this->initBindings(
            $bootloader->defineBindings(),
            $bootloader->defineSingletons()
        );
    }

    /**
     * Bind declared bindings.
     */
    private function initBindings(array $bindings, array $singletons): void
    {
        foreach ($bindings as $aliases => $resolver) {
            $this->container->bind($aliases, $resolver);
        }

        foreach ($singletons as $aliases => $resolver) {
            $this->container->bindSingleton($aliases, $resolver);
        }
    }

    private function invokeBootloader(BootloaderInterface $bootloader, string $method, array $options): void
    {
        $refl = new \ReflectionClass($bootloader);
        if (! $refl->hasMethod($method)) {
            return;
        }

        $boot = new \ReflectionMethod($bootloader, $method);

        $args = $this->container->resolveArguments($boot);
        if (! isset($args['boot'])) {
            $args['boot'] = $options;
        }

        $boot->invokeArgs($bootloader, \array_values($args));
    }

    private function fireCallbacks(array $bootingCallbacks): void
    {
        foreach ($bootingCallbacks as $callback) {
            $callback($this->container);
        }
    }
}
