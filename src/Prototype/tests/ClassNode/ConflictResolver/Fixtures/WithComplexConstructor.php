<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;

class WithComplexConstructor
{
    public function __construct(
        string $str1,
        $var,
        $untypedVarWithDefault = 3,
        &$refVar,
        ATest3 $testApp,
        ?string $str2,
        ?\StdClass $nullableClass1,
        ?Test $test1 = null,
        ?string $str3 = null,
        ?int $int = 123,
        \StdClass $nullableClass2 = null,
        string ...$variadicVar
    ) {
        $var2 = new ATest3();
    }
}
