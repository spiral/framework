<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTestCase;

final class RouteListTest extends ConsoleTestCase
{
    public function testExtensions(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'AuthController'
        ]);
    }
}
