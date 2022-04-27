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
     * @psalm-var list<mixed>
     */
    private array $resolvedValues = [];

    public function __construct(
        public readonly ReflectionFunctionAbstract $reflection,
        private array $arguments,
    ) {
        $this->modeNamed = $this->isNamedMode();
    }

    public function addResolvedValue(mixed &$value): void
    {
        $this->resolvedValues[] = &$value;
    }

    public function resolveParameterByNameOrPosition(ReflectionParameter $parameter, bool $variadic): bool
    {
        $key = $this->modeNamed
            ? $parameter->getName()
            : $parameter->getPosition();

        if (!\array_key_exists($key, $this->arguments)) {
            return false;
        }
        /** @psalm-suppress UnusedVariable */
        $value = &$this->arguments[$key];

        if ($variadic && \is_array($value)) {
            // Save keys is possible
            $positional = true;
            foreach ($value as $key => &$item) {
                if (!$positional && \is_int($key)) {
                    throw new ResolvingException(
                        'Cannot use positional argument after named argument during unpacking named variadic argument.'
                    );
                }
                $positional = $positional && \is_int($key);
                if ($positional) {
                    $this->resolvedValues[] = &$item;
                } else {
                    $this->resolvedValues[$key] = &$item;
                }
            }
        } else {
            $this->addResolvedValue($value);
        }
        return true;
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
