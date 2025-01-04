<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Filters\Config\FiltersConfig;

final class FiltersConfigTest extends TestCase
{
    public function testGetsInterceptors(): void
    {
        $config = new FiltersConfig([
            'interceptors' => $array = ['foo', 'bar'],
        ]);

        self::assertSame($array, $config->getInterceptors());
    }

    public function testGetsInterceptorsWhenKeyIsNotDefined(): void
    {
        $config = new FiltersConfig([]);

        self::assertSame([], $config->getInterceptors());
    }
}
