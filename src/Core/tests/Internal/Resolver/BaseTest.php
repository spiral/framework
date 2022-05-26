<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

abstract class BaseTest extends \Spiral\Tests\Core\Internal\BaseTest
{
    protected function resolveClassConstructor(string $class, array $args = []): mixed
    {
        $classReflection = new \ReflectionClass($class);
        $reflection = $classReflection->getConstructor();
        return $this->createResolver()->resolveArguments($reflection, $args);
    }

    protected function resolveClosure(
        \Closure $closure,
        array $args = [],
        bool $validate = true,
    ): array {
        return $this->createResolver()->resolveArguments(new \ReflectionFunction($closure), $args, $validate);
    }
}
