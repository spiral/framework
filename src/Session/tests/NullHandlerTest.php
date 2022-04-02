<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\TestCase;
use Spiral\Session\Handler\NullHandler;

class NullHandlerTest extends TestCase
{
    public function testNullHandler(): void
    {
        $handler = new NullHandler();

        $this->assertTrue($handler->destroy('abc'));
        $this->assertSame(1, $handler->gc(1));
        $this->assertTrue($handler->open('path', '1'));
        $this->assertSame('', $handler->read(''));
        $this->assertTrue($handler->write('abc', 'data'));
        $this->assertTrue($handler->close());
    }
}
