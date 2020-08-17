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

class ExtensionsCommand extends ConsoleTest
{
    public function testExtensions(): void
    {
        $output = $this->runCommand('php:extensions');

        foreach (get_loaded_extensions() as $extension) {
            $this->assertStringContainsString($extension, $output);
        }
    }
}
