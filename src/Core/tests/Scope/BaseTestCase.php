<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\TestCase;
use WeakMap;

abstract class BaseTestCase extends TestCase
{
    public WeakMap $weakMap;

    protected function setUp(): void
    {
        $this->weakMap = new WeakMap();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        self::assertEmpty($this->weakMap, 'Weak map is not empty.');
        parent::tearDown();
    }
}
