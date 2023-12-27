<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy\Stub;

interface MockInterface
{
    public function bar(string $name): void;

    public function baz(string $name, int $age): string;

    public function qux(string|int $age = 42): string|int;

    public function space(mixed $test age = 42): mixed;

    public function extra(mixed $foo): array;

    public function extraVariadic(mixed ...$foo): array;

    public function concat(string $prefix, string &$byLink): void;

    public function concatMultiple(string $prefix, string &...$byLink): array;
}
