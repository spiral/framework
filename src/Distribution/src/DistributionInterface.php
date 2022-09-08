<?php

declare(strict_types=1);

namespace Spiral\Distribution;

/**
 * @template-implements \IteratorAggregate<string, ResolverInterface>
 */
interface DistributionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param string|null $name
     */
    public function resolver(string $name = null): UriResolverInterface;

    public function withDefault(string $name): self;
}
