<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Command;

use Spiral\Framework\ConsoleTest;

class ConfigureTest extends ConsoleTest
{
    public function testConfigure(): void
    {
        $output = $this->runCommandDebug('configure');

        $this->assertContains('Verifying runtime directory', $output);
        $this->assertContains('locale directory', $output);
    }
}
