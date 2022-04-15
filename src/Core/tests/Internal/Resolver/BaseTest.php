<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Internal\Config;
use Spiral\Core\Internal\Constructor;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;

abstract class BaseTest extends TestCase
{
    protected Constructor $constructor;
    protected Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Constructor($this->config, [
            'state' => new State(),
        ]);
        parent::setUp();
    }

    protected function resolveClosure(\Closure $closure, array $args = []): mixed
    {
        return $this->createResolver()->resolveArguments(new \ReflectionFunction($closure), $args);
    }

    protected function createResolver(): ResolverInterface
    {
        return new Resolver($this->constructor);
    }
}
