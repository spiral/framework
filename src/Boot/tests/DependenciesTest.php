<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Core\Container;

class DependenciesTest extends TestCase
{
    public function testDep(): void
    {
        $c = new Container();

        $b = new BootloadManager($c);

        $b->bootload([BootloaderA::class]);

        $this->assertTrue($c->has('a'));
        $this->assertFalse($c->has('b'));
    }

    public function testDep2(): void
    {
        $c = new Container();

        $b = new BootloadManager($c);

        $b->bootload([BootloaderB::class]);

        $this->assertTrue($c->has('a'));
        $this->assertTrue($c->has('b'));
    }
}
