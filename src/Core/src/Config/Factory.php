<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

/**
 * Make a value using a closure.
 */
final class Factory extends Binding
{
    use ClosureRendererTrait;

    /** @var class-string|null */
    private readonly ?string $returnClass;
    public readonly \Closure $factory;
    private readonly int $parametersCount;
    private ?string $definition;

    public function __construct(
        callable $callable,
        public readonly bool $singleton = false,
    ) {
        $this->factory = $callable(...);
        $reflection = new \ReflectionFunction($this->factory);
        $this->parametersCount = $reflection->getNumberOfParameters();

        // Detect the return type of the factory
        $returnType = (string) $reflection->getReturnType();
        $this->returnClass = \class_exists($returnType) ? $returnType : null;

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

    public function getParametersCount(): int
    {
        return $this->parametersCount;
    }

    public function __toString(): string
    {
        $this->definition ??= $this->renderClosureSignature(new \ReflectionFunction($this->factory));

        return \sprintf(
            'Factory from %s',
            $this->definition,
        );
    }

    /**
     * @return class-string|null
     * @internal
     */
    public function getReturnClass(): ?string
    {
        return $this->returnClass;
    }
}
