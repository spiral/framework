<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;

class Params
{
    /**
     * @param string $method
     *
     * @return \ReflectionParameter[]
     */
    public static function getParams(string $method): array
    {
        try {
            $rc = new \ReflectionClass(self::class);
            $method = $rc->getMethod($method);

            return $method->getParameters();
        } catch (\ReflectionException $e) {
            return [];
        }
    }

    private function paramsSource(
        Some $t1,
        Some $t4,
        ?TestAlias $a1,
        SubFolder\Some $st = null,
        string $str = 'value'
    ): void {
    }

    private function paramsSource2(
        Some $t1,
        Some $t4,
        ?TestAlias $a1,
        SubFolder\Some $st = null,
        string $t2 = 'value'
    ): void {
    }

    private function paramsSource3(Some $t, Some $t4, ?TestAlias $a1, SubFolder\Some $st = null): void
    {
    }
}
