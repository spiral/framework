<?php

declare(strict_types=1);

namespace Framework\Console;

use Spiral\Tests\Framework\ConsoleTest;

final class CommandDescriptionTest extends ConsoleTest
{
    public function testAllCommandsShouldHaveDescription(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(command: 'list', strings: [
            'Dump the shell completion script',
            'Configure project',
            'Display help for a command',
            'List commands',
            'Update project state',
            'Clean application runtime cache',
            'Create bootloader declaration',
            'Create command declaration',
            'Create config declaration',
            'Create controller declaration',
            'Create job handler declaration',
            'Create middleware declaration',
            'Generate new encryption key',
            'Dump given locale using specified dumper and path',
            'Index all declared translation strings and usages',
            'Reset translation cache',
            'Dump all prototyped dependencies as PrototypeTrait DOCComment',
            'Inject all prototype dependencies',
            'List all prototyped classes',
            'List application routes',
            'Warm-up view cache',
            'Clear view cache',
        ]);
    }
}
