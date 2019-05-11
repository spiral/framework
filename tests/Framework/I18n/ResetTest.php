<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\I18n;

use Spiral\Framework\ConsoleTest;

class ResetTest extends ConsoleTest
{
    public function testReset()
    {
        $this->runCommandDebug('i18n:index');
        $output = $this->runCommandDebug('i18n:reset');
        $this->assertContains('cache has been reset', $output);
    }
}