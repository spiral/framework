<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Proxy\Stub;

use Spiral\Core\Internal\Proxy\ProxyTrait;

interface MockInterface
{
    /**
     * Mustn't be a part of the {@see ProxyTrait}
     */
    public static function resolve(): void;

    public function bar(string $name): void;

    public function baz(string $name, int $age): string;

    public function qux(string|int $age = 42): string|int;

    public function space(mixed $test age = 42): mixed;

    public function extra(mixed $foo): array;

    public function extraVariadic(mixed ...$foo): array;

    public function concat(string $prefix, string &$byLink): array;

    public function concatMultiple(string $prefix, string &...$byLink): array;

    public function &same(string &$byLink): string;
}
