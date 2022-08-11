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
use Spiral\Boot\Exception\ClassNotFoundException;
use Spiral\Core\Container;

/**
 * Provides ability to bootload ServiceProviders.
 */
final class BootloadManager implements Container\SingletonInterface
{
    /* @var Container @internal */
    protected $container;

    /** @var array<class-string> */
    private $classes = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get bootloaded classes.
     * @return array<class-string>
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
     * @param array<class-string>|array<class-string,array<string,mixed>> $classes
     * @param array<Closure> $staringCallbacks
     * @param array<Closure> $startedCallbacks
     *
     * @throws \Throwable
     */
    public function bootload(array $classes, array $staringCallbacks = [], array $startedCallbacks = []): void
    {
        $this->container->runScope(
            [self::class => $this],
            function () use ($classes, $staringCallbacks, $startedCallbacks): void {
                $this->boot($classes, $staringCallbacks, $startedCallbacks);
            }
        );
    }

    /**
     * Bootloader all given classes.
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     *
     * @throws \Throwable
     */
    protected function boot(array $classes, array $startingCallbacks, array $startedCallbacks): void
    {
        $bootloaders = \iterator_to_array($this->initBootloaders($classes));

        $this->fireCallbacks($startingCallbacks);

        foreach ($bootloaders as $data) {
            $bootloader = $data['bootloader'];
            $options = $data['options'];
            $this->invokeBootloader($bootloader, 'start', $options);
        }

        $this->fireCallbacks($startedCallbacks);
    }

    /**
     * Resolve all bootloader dependencies and init bindings
     */
    protected function initBootloader(BootloaderInterface $bootloader): iterable
    {
        if ($bootloader instanceof DependedInterface) {
            yield from $this->initBootloaders($bootloader->defineDependencies());
        }

        $this->initBindings(
            $bootloader->defineBindings(),
            $bootloader->defineSingletons()
        );
    }

    /**
     * Instantiate bootloader objects and resolve dependencies
     *
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     */
    private function initBootloaders(array $classes): \Generator
    {
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
                throw new ClassNotFoundException(
                    \sprintf('Bootloader class `%s` is not exist.', $class)
                );
            }

            if (\in_array($class, $this->classes, true)) {
                continue;
            }

            $this->classes[] = $class;
            $bootloader = $this->container->get($class);

            if (!$bootloader instanceof BootloaderInterface) {
                continue;
            }

            yield from $this->initBootloader($bootloader);
            $this->invokeBootloader($bootloader, 'boot', $options);

            yield $class => \compact('bootloader', 'options');
        }
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
        if (!$refl->hasMethod($method)) {
            return;
        }

        $boot = new \ReflectionMethod($bootloader, $method);

        $args = $this->container->resolveArguments($boot);
        if (!isset($args['boot'])) {
            $args['boot'] = $options;
        }

        $boot->invokeArgs($bootloader, \array_values($args));
    }

    /**
     * @param array<Closure> $callbacks
     */
    private function fireCallbacks(array $callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->container->invoke($callback);
        }
    }
}
