<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Spiral\Core\BinderInterface;
use Spiral\Core\Internal\Config;
use Spiral\Core\Internal\Constructor;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\ResolverInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;

class BaseTest extends TestCase
{
    protected Constructor $constructor;

    public function testNullableDefaultNull(): void
    {
        $result = $this->resolveClosure(static fn (?string $param = null) => $param);

        $this->assertSame([null], $result);
    }

    public function testNullableDefaultScalar(): void
    {
        $resolver = $this->createResolver();
        $reflection = new \ReflectionFunction(static fn (?string $param = 'scalar') => $param);

        $result = $resolver->resolveArguments($reflection);

        $this->assertSame(['scalar'], $result);
    }


    // todo: Intersection

    // todo: Union

    // todo: Links



    protected function resolveClosure(\Closure $closure, array $args = []): mixed
    {
        return $this->createResolver()->resolveArguments(new \ReflectionFunction($closure), $args);
    }

    protected function createResolver(): ResolverInterface
    {
        $config = new Config();
        $this->constructor = $constructor = new Constructor($config);
        return new Resolver($constructor);
    }
}
