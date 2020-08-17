<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTest;

class RouteListTest extends ConsoleTest
{
    public function testExtensions(): void
    {
        $output = $this->runCommand('route:list');

        $this->assertStringContainsString('AuthController', $output);
    }
}
