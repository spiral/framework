<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy\Stub;

final class MockInterfaceImpl implements MockInterface, EmptyInterface
{
    public static function resolve(): void {}

    public function bar(string $name): void {}

    public function baz(string $name, int $age): string
    {
        return $name;
    }

    public function qux(int|string $age = 42): string|int
    {
        return $age;
    }

    public function space(mixed $test age = 42): mixed
    {
        return $test age;
    }

    public function extra(mixed $foo): array
    {
        return \func_get_args();
    }

    public function extraVariadic(mixed ...$foo): array
    {
        return $foo;
    }

    public function concat(string $prefix, string &$byLink): array
    {
        $byLink = $prefix . $byLink;
        return \func_get_args();
    }

    public function &same(string &$byLink): string
    {
        return $byLink;
    }

    public function concatMultiple(string $prefix, string &...$byLink): array
    {
        foreach ($byLink as $k => $link) {
            $byLink[$k] = $prefix . $link;
            unset($link);
        }

        return $byLink;
    }

    public function staticType(): static
    {
        return $this;
    }

    public function selfType(): MockInterface
    {
        return $this;
    }
}
