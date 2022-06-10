<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\I18n;

use Spiral\Tests\Framework\ConsoleTest;

final class ResetTest extends ConsoleTest
{
    public function testReset(): void
    {
        $this->runCommand('i18n:index');
        $this->assertConsoleCommandOutputContainsStrings('i18n:reset', strings: 'cache has been reset');
    }
}
