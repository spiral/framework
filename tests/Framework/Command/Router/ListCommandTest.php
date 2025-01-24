<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Router;

use Spiral\Tests\Framework\ConsoleTestCase;

final class ListCommandTest extends ConsoleTestCase
{
    public function testExtensions(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('route:list', strings: [
            'AuthController',
        ]);
    }
}
