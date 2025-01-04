<?php

declare(strict_types=1);

namespace Command;

use Spiral\Tests\Scaffolder\Command\AbstractCommandTestCase;

final class InfoCommandTest extends AbstractCommandTestCase
{
    public function testInfo(): void
    {
        $result = $this->console()->run('scaffolder:info')->getOutput()->fetch();

        $strings = [
            'Scaffolder commands',
            'create:controller',
            'create:bootloader',
            'create:config',
            'create:filter',
            'create:command',
            'create:middleware',
            'create:jobHandler',
        ];

        foreach ($strings as $string) {
            self::assertStringContainsString($string, $result);
        }
    }
}
