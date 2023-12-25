<?php

declare(strict_types=1);

namespace Framework\Console\Command\Tokenizer;

use Spiral\Console\CommandLocatorListener;
use Spiral\Prototype\PrototypeLocatorListener;
use Spiral\Queue\JobHandlerLocatorListener;
use Spiral\Queue\SerializerLocatorListener;
use Spiral\Tests\Framework\ConsoleTestCase;

final class InfoCommandTest extends ConsoleTestCase
{
    public function testInfoCommand(): void
    {
        $this->assertConsoleCommandOutputContainsStrings(command: 'tokenizer:info', strings: [
            'Included directories:',
            'app',
            'Excluded directories:',
            'app/resources/',
            'app/config/',
            'tests',
            'migrations',
            'Loaders:',
            'Classes',
            'enabled',
            'Enums',
            'disabled. To enable, add "TOKENIZER_LOAD_ENUMS=true" to your .env file.',
            'Interfaces',
            'disabled. To enable, add "TOKENIZER_LOAD_INTERFACES=true" to your .env file.',
            'Listeners:',
            CommandLocatorListener::class,
            'Console/src/CommandLocatorListener.php',
            JobHandlerLocatorListener::class,
            'Queue/src/JobHandlerLocatorListener.php',
            SerializerLocatorListener::class,
            'Queue/src/SerializerLocatorListener.php',
            PrototypeLocatorListener::class,
            'Prototype/src/PrototypeLocatorListener.php',
            'Tokenizer cache: disabled',
            'To enable cache, add "TOKENIZER_CACHE_TARGETS=true" to your .env file.',
        ]);
    }
}
