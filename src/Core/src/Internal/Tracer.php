<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
final class Tracer implements \Stringable
{
    /**
     * @var Trace[]
     */
    private array $traces = [];

    public function __toString(): string
    {
        $result = [];
        if ($this->traces !== []) {
            $result[] = 'Container trace list:';

            foreach ($this->traces as $item) {
                $result[] = (string) $item;
            }
        }

        return \implode(PHP_EOL, $result);
    }

    public function traceAutowire(string $alias, string $context = null): void
    {
        $this->trace($alias, 'Autowiring', $context);
    }

    public function traceBinding(string $alias, string|array|object $binding, string $context = null): void
    {
        $message = match (true) {
            \is_string($binding) => \sprintf('Binding found `%s`', $binding),
            \is_object($binding) => \sprintf('Binding found, the instance of `%s`', $binding::class),
            default => 'Binding found'
        };

        $this->trace($alias, $message, $context);
    }

    public function getRootConstructedClass(): string
    {
        return $this->traces[0]->alias;
    }

    public function clean(): void
    {
        $this->traces = [];
    }

    private function trace(string $alias, string $information, string $context = null): void
    {
        $this->traces[] = new Trace($alias, $information, $context);
    }
}
