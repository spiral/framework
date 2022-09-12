<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Closure;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\BootloadManagerInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Core\ScopeInterface;

/**
 * Provides ability to bootload ServiceProviders.
 */
final class BootloadManager implements BootloadManagerInterface, Container\SingletonInterface
{
    public function __construct(
        /* @internal */
        private readonly ScopeInterface $scope,
        private readonly InvokerInterface $invoker,
        private readonly ResolverInterface $resolver,
        private readonly Initializer $initializer
    ) {
    }

    public function getClasses(): array
    {
        return $this->initializer->getRegistry()->getClasses();
    }

    public function bootload(array $classes, array $bootingCallbacks = [], array $bootedCallbacks = []): void
    {
        $this->scope->runScope(
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

        $args = $this->resolver->resolveArguments($method);
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
            $this->invoker->invoke($callback);
        }
    }
}
