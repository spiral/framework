<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework;

class ConfigureTest extends ConsoleTest
{
    public function testConfigure(): void
    {
        $output = $this->runCommandDebug('configure');

        $this->assertStringContainsString('Verifying runtime directory', $output);
        $this->assertStringContainsString('locale directory', $output);
    }
}
