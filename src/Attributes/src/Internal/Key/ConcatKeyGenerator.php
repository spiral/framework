<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @var array<KeyGeneratorInterface>
     */
    private $generators;

    /**
     * @var string
     */
    private $join;

    /**
     * @param array<KeyGeneratorInterface> $generators
     */
    public function __construct(array $generators, string $join = self::JOIN_DELIMITER)
    {
        $this->generators = $generators;
        $this->join = $join;
    }

    /**
     * {@inheritDoc}
     */
    public function forClass(\ReflectionClass $class): string
    {
        return $this->joinBy(static function (KeyGeneratorInterface $generator) use ($class): string {
            return $generator->forClass($class);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function forProperty(\ReflectionProperty $prop): string
    {
        return $this->joinBy(static function (KeyGeneratorInterface $generator) use ($prop): string {
            return $generator->forProperty($prop);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function forConstant(\ReflectionClassConstant $const): string
    {
        return $this->joinBy(static function (KeyGeneratorInterface $generator) use ($const): string {
            return $generator->forConstant($const);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function forFunction(\ReflectionFunctionAbstract $fn): string
    {
        return $this->joinBy(static function (KeyGeneratorInterface $generator) use ($fn): string {
            return $generator->forFunction($fn);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function forParameter(\ReflectionParameter $param): string
    {
        return $this->joinBy(static function (KeyGeneratorInterface $generator) use ($param): string {
            return $generator->forParameter($param);
        });
    }

    /**
     * @param \Closure(KeyGeneratorInterface): string $each
     * @return string
     */
    private function joinBy(\Closure $each): string
    {
        $result = [];

        foreach ($this->generators as $generator) {
            $result[] = $each($generator);
        }

        return \implode($this->join, $result);
    }
}
