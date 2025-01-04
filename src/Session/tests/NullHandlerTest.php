<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\TestCase;
use Spiral\Session\Handler\NullHandler;

class NullHandlerTest extends TestCase
{
    public function testNullHandler(): void
    {
        $handler = new NullHandler();

        self::assertTrue($handler->destroy('abc'));
        self::assertSame(1, $handler->gc(1));
        self::assertTrue($handler->open('path', '1'));
        self::assertSame('', $handler->read(''));
        self::assertTrue($handler->write('abc', 'data'));
        self::assertTrue($handler->close());
    }
}
