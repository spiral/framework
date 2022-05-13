<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Closure;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Core\Container;

/**
 * Provides ability to bootload ServiceProviders.
 */
final class BootloadManager implements Container\SingletonInterface
{
    public function __construct(
        /* @internal */
        private readonly Container $container,
        private Initializer $initializer
    ) {
    }

    /**
     * Get bootloaded classes.
     */
    public function getClasses(): array
    {
        return $this->initializer->getRegistry()->getClasses();
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
     * @param array<Closure> $bootingCallbacks
     * @param array<Closure> $bootedCallbacks
     *
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
     * @param array<class-string>|array<class-string, array<string,mixed>> $classes
     *
     * @throws \Throwable
     */
    protected function boot(array $classes, array $bootingCallbacks, array $bootedCallbacks): void
    {
        $bootloaders = \iterator_to_array($this->initializer->init($classes));

        foreach ($bootloaders as $data) {
            $this->invokeBootloader($data['bootloader'], Methods::INIT, $data['options']);
        }

        $this->fireCallbacks($bootingCallbacks);

        foreach ($bootloaders as $data) {
            $this->invokeBootloader($data['bootloader'], Methods::BOOT, $data['options']);
        }

        $this->fireCallbacks($bootedCallbacks);
    }

    private function invokeBootloader(BootloaderInterface $bootloader, Methods $method, array $options): void
    {
        $refl = new \ReflectionClass($bootloader);
        if (!$refl->hasMethod($method->value)) {
            return;
        }

        $method = $refl->getMethod($method->value);

        $args = $this->container->resolveArguments($method);
        if (!isset($args['boot'])) {
            $args['boot'] = $options;
        }

        $method->invokeArgs($bootloader, \array_values($args));
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
