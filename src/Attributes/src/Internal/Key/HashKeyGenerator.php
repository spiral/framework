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
 * A generator that hashes the key. It can be used when the cache driver
 * cannot accept string keys containing special characters (that is, not
 * containing letters and numbers).
 *
 * @internal HashKeyGenerator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class HashKeyGenerator implements KeyGeneratorInterface
{
    /**
     * Hashing using md5 is the fastest 128-bit algorithm.
     *
     * However, in the case of small projects, it is recommended to use
     * crc32, which significantly increases performance, but cannot provide
     * a similarly variable result.
     *
     * @var string
     */
    private const DEFAULT_HASH_ALGO = 'md5';

    /**
     * @var KeyGeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $algo;

    /**
     * @param KeyGeneratorInterface|null $base
     * @param string $algo
     */
    public function __construct(KeyGeneratorInterface $base, string $algo = self::DEFAULT_HASH_ALGO)
    {
        $this->algo = $algo;
        $this->generator = $base;
    }

    /**
     * {@inheritDoc}
     */
    public function forClass(\ReflectionClass $class): string
    {
        return \hash($this->algo, $this->generator->forClass($class));
    }

    /**
     * {@inheritDoc}
     */
    public function forProperty(\ReflectionProperty $prop): string
    {
        return \hash($this->algo, $this->generator->forProperty($prop));
    }

    /**
     * {@inheritDoc}
     */
    public function forConstant(\ReflectionClassConstant $const): string
    {
        return \hash($this->algo, $this->generator->forConstant($const));
    }

    /**
     * {@inheritDoc}
     */
    public function forFunction(\ReflectionFunctionAbstract $fn): string
    {
        return \hash($this->algo, $this->generator->forFunction($fn));
    }

    /**
     * {@inheritDoc}
     */
    public function forParameter(\ReflectionParameter $param): string
    {
        return \hash($this->algo, $this->generator->forParameter($param));
    }
}
