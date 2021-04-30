<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Unit;

use Spiral\Tests\Storage\TestCase;
use Spiral\Tests\Storage\Traits\ReflectionHelperTrait;
use Spiral\Tests\Storage\Traits\StorageConfigTrait;

abstract class UnitTestCase extends TestCase
{
    use ReflectionHelperTrait;
    use StorageConfigTrait;
}
