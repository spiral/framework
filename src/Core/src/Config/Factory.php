<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Make a value using a closure.
 */
final class Factory extends Binding
{
    use \Spiral\Core\Exception\Traits\ClosureRendererTrait;

    public readonly \Closure $factory;
    private readonly int $parametersCount;
    private ?string $definition;

    public function __construct(
        callable $callable,
        public readonly bool $singleton = false,
    ) {
        $this->factory = $callable(...);
        $this->parametersCount = (new \ReflectionFunction($this->factory))->getNumberOfParameters();
        /** @psalm-suppress TypeDoesNotContainType */
        $this->definition = match (true) {
            \is_string($callable) => $callable,
            \is_array($callable) => \sprintf(
                '%s::%s()',
                \is_object($callable[0]) ? $callable[0]::class : $callable[0],
                $callable[1],
            ),
            \is_object($callable) && $callable::class !== \Closure::class => 'object ' . $callable::class,
            default => null,
        };
    }

    public function __toString(): string
    {
        $this->definition ??= $this->renderClosureSignature(new \ReflectionFunction($this->factory));

        return \sprintf(
            'Factory from %s',
            $this->definition,
        );
    }

    public function getParametersCount(): int
    {
        return $this->parametersCount;
    }
}
