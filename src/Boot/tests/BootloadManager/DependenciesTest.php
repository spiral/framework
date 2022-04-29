<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;

class DependenciesTest extends TestCase
{
    public function testDep(): void
    {
        $c = new Container();

        $b = new BootloadManager($c, new Initializer($c));

        $b->bootload([BootloaderA::class]);

        $this->assertTrue($c->has('a'));
        $this->assertFalse($c->has('b'));
    }

    public function testDep2(): void
    {
        $c = new Container();

        $b = new BootloadManager($c, new Initializer($c));

        $b->bootload([BootloaderB::class]);

        $this->assertTrue($c->has('a'));
        $this->assertTrue($c->has('b'));
    }
}
