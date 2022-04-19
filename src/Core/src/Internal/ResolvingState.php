<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Generator;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * Intermediate arguments resolving data to pass around until resolving is finished.
 *
 * @internal
 */
final class ResolvingState
{
    public readonly ReflectionFunctionAbstract $reflection;

    public readonly bool $modeNamed;

    /**
     * @psalm-var array<int, object>
     */
    private array $positionArguments = [];

    /**
     * @psalm-var array<string, mixed>
     */
    private array $namedArguments = [];

    /**
     * @psalm-var list<mixed>
     */
    private array $resolvedValues = [];

    /**
     * @param ReflectionFunctionAbstract $reflection Function reflection.
     * @param array $arguments User arguments.
     */
    public function __construct(ReflectionFunctionAbstract $reflection, array $arguments)
    {
        $this->reflection = $reflection;
        $this->shouldPushTrailingArguments = !$reflection->isInternal();
        $this->sortArguments($arguments);
        $this->modeNamed = \count($this->positionArguments) === 0;
        if (!$this->modeNamed && \count($this->namedArguments) !== 0) {
            throw new \Exception();
        }
    }

    public function addResolvedValue(mixed &$value): void
    {
        $this->resolvedValues[] = &$value;
    }

    public function resolveParameterByNameOrPosition(ReflectionParameter $parameter, bool $variadic): bool
    {
        if ($this->modeNamed) {
            $name = $parameter->getName();
            if (!\array_key_exists($name, $this->namedArguments)) {
                return false;
            }
            $value = &$this->namedArguments[$name];
        } else {
            $pos = $parameter->getPosition();
            if (!\array_key_exists($pos, $this->positionArguments)) {
                return false;
            }
            $value = &$this->positionArguments[$pos];
        }

        if ($variadic && \is_array($value)) {
            \array_walk($value, [$this, 'addResolvedValue']);
        } else {
            $this->addResolvedValue($value);
        }
        return true;
    }

    public function getResolvedValues(): array
    {
        return $this->resolvedValues;
    }

    private function sortArguments(array $arguments): void
    {
        foreach ($arguments as $key => &$value) {
            if (\is_int($key)) {
                $this->positionArguments[$key] = &$value;
            } else {
                $this->namedArguments[$key] = &$value;
            }
        }
    }
}
