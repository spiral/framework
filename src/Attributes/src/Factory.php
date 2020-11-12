<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Spiral\Attributes\Doctrine\DoctrineResolver;
use Spiral\Attributes\Native\NativeResolver;
use Spiral\Attributes\Selective\SelectiveResolver;

class Factory implements FactoryInterface
{
    /**
     * @var string
     */
    private const ERROR_DRIVER_NOT_FOUND = 'No registered driver resolver for type #%d';

    /**
     * @var string
     */
    private const ERROR_DRIVER_NOT_AVAILABLE = 'There are no metadata readers available';

    /**
     * @var array|ResolverInterface[]
     */
    private $resolvers;

    /**
     * @param array|ResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $this->getDefaultResolvers();

        foreach ($resolvers as $id => $resolver) {
            $this->resolvers[$id] = $resolver;
        }
    }

    /**
     * @psalm-param FactoryInterface::PREFER_* $type
     *
     * @param int $type
     * @return ReaderInterface
     */
    public function create(int $type = self::PREFER_SELECTIVE): ReaderInterface
    {
        \ksort($this->resolvers);

        $resolver = $this->resolvers[$type] ?? null;

        if ($resolver === null) {
            throw new \LogicException(\sprintf(self::ERROR_DRIVER_NOT_FOUND, $type));
        }

        if ($resolver->isSupported()) {
            return $resolver->create();
        }

        return $this->createExcept($type);
    }

    /**
     * @return ResolverInterface[]
     */
    private function getDefaultResolvers(): array
    {
        $result = [
            static::PREFER_NATIVE   => new NativeResolver(),
            static::PREFER_DOCTRINE => new DoctrineResolver(),
        ];

        $result[static::PREFER_SELECTIVE] = new SelectiveResolver($result);

        return $result;
    }

    /**
     * @param int $type
     * @return ReaderInterface
     */
    private function createExcept(int $type): ReaderInterface
    {
        foreach ($this->resolvers as $id => $resolver) {
            if ($id === $type) {
                continue;
            }

            if ($resolver->isSupported()) {
                return $resolver->create();
            }
        }

        throw new \LogicException(self::ERROR_DRIVER_NOT_AVAILABLE);
    }
}
