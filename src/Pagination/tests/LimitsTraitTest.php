<?php

declare(strict_types=1);

namespace Spiral\Tests\Pagination;

use PHPUnit\Framework\TestCase;
use Spiral\Pagination\Traits\LimitsTrait;

/**
 * Class LimitTraitTest
 *
 * @package Spiral\Tests\Pagination\Traits
 */
class LimitsTraitTest extends TestCase
{
    public const DEFAULT_LIMIT = 0;
    public const DEFAULT_OFFSET = 0;
    public const LIMIT = 10;
    public const OFFSET = 15;

    /**
     * @var LimitsTrait
     */
    private $trait;

    public function setUp(): void
    {
        $this->trait = $this->getMockForTrait(LimitsTrait::class);
    }

    public function testLimit()
    {
        $this->assertEquals(static::DEFAULT_LIMIT, $this->trait->getLimit());
        $this->assertEquals($this->trait, $this->trait->limit(static::LIMIT));
        $this->assertEquals(static::LIMIT, $this->trait->getLimit());
    }

    public function testOffset()
    {
        $this->assertEquals(static::DEFAULT_OFFSET, $this->trait->getOffset());
        $this->assertEquals($this->trait, $this->trait->offset(static::OFFSET));
        $this->assertEquals(static::OFFSET, $this->trait->getOffset());
    }
}
