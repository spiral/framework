<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\I18n;

use Spiral\Tests\Framework\ConsoleTest;

class ResetTest extends ConsoleTest
{
    public function testReset(): void
    {
        $this->runCommandDebug('i18n:index');
        $output = $this->runCommandDebug('i18n:reset');
        $this->assertStringContainsString('cache has been reset', $output);
    }
}
