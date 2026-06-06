<?php

declare(strict_types=1);

namespace Spiral\Tests\Pagination;

use PHPUnit\Framework\TestCase;
use Spiral\Pagination\Traits\LimitsTrait;

/**
 * @package Spiral\Tests\Pagination\Traits
 */
final class LimitsTraitTest extends TestCase
{
    public const DEFAULT_LIMIT = 0;
    public const DEFAULT_OFFSET = 0;
    public const LIMIT = 10;
    public const OFFSET = 15;

    private object $trait;

    public function testLimit(): void
    {
        self::assertEquals(self::DEFAULT_LIMIT, $this->trait->getLimit());
        self::assertEquals($this->trait, $this->trait->limit(self::LIMIT));
        self::assertEquals(self::LIMIT, $this->trait->getLimit());
    }

    public function testOffset(): void
    {
        self::assertEquals(self::DEFAULT_OFFSET, $this->trait->getOffset());
        self::assertEquals($this->trait, $this->trait->offset(self::OFFSET));
        self::assertEquals(self::OFFSET, $this->trait->getOffset());
    }

    protected function setUp(): void
    {
        $this->trait = new class {
            use LimitsTrait;
        };
    }
}
