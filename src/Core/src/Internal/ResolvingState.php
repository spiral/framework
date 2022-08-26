<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use ReflectionFunctionAbstract;
use ReflectionParameter;
use Spiral\Core\Exception\Resolver\ResolvingException;

/**
 * @internal
 */
final class ResolvingState
{
    public readonly bool $modeNamed;

    /**
     * @psalm-var array<array-key, mixed>
     */
    private array $resolvedValues = [];

    public function __construct(
        public readonly ReflectionFunctionAbstract $reflection,
        private array $arguments,
    ) {
        $this->modeNamed = $this->isNamedMode();
    }

    public function addResolvedValue(mixed &$value, string $key = null): void
    {
        if ($key === null) {
            $this->resolvedValues[] = &$value;
        } else {
            $this->resolvedValues[$key] = &$value;
        }
    }

    public function resolveParameterByNameOrPosition(ReflectionParameter $parameter, bool $variadic): array
    {
        $key = $this->modeNamed
            ? $parameter->getName()
            : $parameter->getPosition();

        if (!\array_key_exists($key, $this->arguments)) {
            return [];
        }
        $_val = &$this->arguments[$key];

        if ($variadic && \is_array($_val)) {
            // Save keys is possible
            $positional = true;
            $result = [];
            foreach ($_val as $key => &$item) {
                if (!$positional && \is_int($key)) {
                    throw new ResolvingException(
                        'Cannot use positional argument after named argument during unpacking named variadic argument.'
                    );
                }
                $positional = $positional && \is_int($key);
                if ($positional) {
                    $result[] = &$item;
                } else {
                    $result[$key] = &$item;
                }
            }
            return $result;
        }
        return [&$_val];
    }

    public function getResolvedValues(): array
    {
        return $this->resolvedValues;
    }

    private function isNamedMode(): bool
    {
        $nums = 0;
        $strings = 0;
        foreach ($this->arguments as $key => $_) {
            if (\is_int($key)) {
                ++$nums;
            } else {
                ++$strings;
            }
        }

        return match (true) {
            $nums === 0 => true,
            $strings === 0 => false,
            default => throw new ResolvingException(
                'You can not use both numeric and string keys for predefined arguments.'
            )
        };
    }
}
