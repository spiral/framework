<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Tag;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tests\Core\Tag\Stub\FileLogger;
use Spiral\Tests\Core\Tag\Stub\KVLogger;

final class UseCaseTest extends TestCase
{
    public function testOneTaggedInstanceViaFirstLevelAttribute(): void
    {
        $c = new Container();
        $c->bindSingleton(KVLogger::class, KVLogger::class);

        $r1 = $c->getTag('logger', resolve: false);
        $r2 = $c->getTag('logger', resolve: true);
        $r3 = $c->getTag('logger', resolve: false);

        // Test `resolve` parameter
        $this->assertCount(0, $r1);
        $this->assertCount(1, $r2);
        // The same logger must be returned because it is singleton that was resolved before
        $this->assertCount(1, $r3);
        $this->assertSame($r2, $r3);
        $this->assertInstanceOf(KVLogger::class, $c->getTag('logger')[0]);
    }

    public function testFewTaggedInstancesViaFirstLevelAttribute(): void
    {
        $c = new Container();
        $c->bindSingleton(KVLogger::class, KVLogger::class);
        $c->bindSingleton(FileLogger::class, FileLogger::class);

        $r1 = $c->getTag('logger', resolve: false);
        $r2 = $c->getTag('logger', resolve: true);
        $r3 = $c->getTag('logger', resolve: false);

        // Test `resolve` parameter
        $this->assertCount(0, $r1);
        $this->assertCount(2, $r2);
        // The same logger must be returned because it is singleton that was resolved before
        $this->assertCount(2, $r3);
        $this->assertSame($r2, $r3);
    }
}
