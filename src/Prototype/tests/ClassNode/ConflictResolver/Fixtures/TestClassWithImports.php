<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;

//this is an alias which should be inserted as a dependency type
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\Some as FTest;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\SubFolder\Some as TestAlias;
//
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\TestAlias as ATest3;
use Spiral\Tests\Prototype\Fixtures\TestApp as Test;
use Spiral\Prototype\Traits\PrototypeTrait;

class TestClassWithImports
{
}
