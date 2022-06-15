<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTest;

final class RouteListTest extends ConsoleTest
{
    public function testExtensions(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'AuthController'
        ]);
    }
}
