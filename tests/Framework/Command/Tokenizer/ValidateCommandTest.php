<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Command\Tokenizer;

use Spiral\App\Tokenizer\InvalidListener;
use Spiral\Console\CommandLocatorListener;
use Spiral\Prototype\PrototypeLocatorListener;
use Spiral\Queue\JobHandlerLocatorListener;
use Spiral\Queue\SerializerLocatorListener;
use Spiral\Tests\Framework\ConsoleTestCase;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

final class ValidateCommandTest extends ConsoleTestCase
{
    public function testValidate(): void
    {
        $this->beforeBooting(function (TokenizerListenerBootloader $tokenizer): void {
            $tokenizer->addListener(new InvalidListener());
        });
        $this->initApp();

        $this->assertConsoleCommandOutputContainsStrings('tokenizer:validate', strings: [
            InvalidListener::class,
            'app/src/Tokenizer/InvalidListener.php',
            'Add #[TargetClass] or #[TargetAttribute] attribute to the listener',
            CommandLocatorListener::class,
            'Console/src/CommandLocatorListener.php',
            '✓',
            JobHandlerLocatorListener::class,
            'Queue/src/JobHandlerLocatorListener.php',
            '✓',
            SerializerLocatorListener::class,
            'Queue/src/SerializerLocatorListener.php',
            '✓',
            PrototypeLocatorListener::class,
            'Prototype/src/PrototypeLocatorListener.php',
            '✓',
        ]);
    }
}
