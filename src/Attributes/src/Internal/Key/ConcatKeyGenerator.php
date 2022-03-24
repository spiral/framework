<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Key;

/**
 * An implementation of a key generator that combines multiple generators
 * and returns a composite identifier.
 *
 * This implementation can be used to combine a unique key generator
 * for the reflection object ({@see NameKeyGenerator}) and a generator that
 * returns the key of the last file modification ({@see ModificationTimeKeyGenerator}).
 *
 * @internal ConcatKeyGenerator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class ConcatKeyGenerator implements KeyGeneratorInterface
{
    /**
     * @var string
     */
    private const JOIN_DELIMITER = '_';

    /**
     * @param array<KeyGeneratorInterface> $generators
     */
    public function __construct(
        private readonly array $generators,
        private readonly string $join = self::JOIN_DELIMITER
    ) {
    }

    public function forClass(\ReflectionClass $class): string
    {
        return $this->joinBy(static fn (KeyGeneratorInterface $generator): string => $generator->forClass($class));
    }

    public function forProperty(\ReflectionProperty $prop): string
    {
        return $this->joinBy(static fn (KeyGeneratorInterface $generator): string => $generator->forProperty($prop));
    }

    public function forConstant(\ReflectionClassConstant $const): string
    {
        return $this->joinBy(static fn (KeyGeneratorInterface $generator): string => $generator->forConstant($const));
    }

    public function forFunction(\ReflectionFunctionAbstract $fn): string
    {
        return $this->joinBy(static fn (KeyGeneratorInterface $generator): string => $generator->forFunction($fn));
    }

    public function forParameter(\ReflectionParameter $param): string
    {
        return $this->joinBy(static fn (KeyGeneratorInterface $generator): string => $generator->forParameter($param));
    }

    private function joinBy(\Closure $each): string
    {
        $result = [];

        foreach ($this->generators as $generator) {
            $result[] = $each($generator);
        }

        return \implode($this->join, $result);
    }
}
