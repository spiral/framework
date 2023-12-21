<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework\Command\Tokenizer;

use Spiral\App\Tokenizer\InvalidListener;
use Spiral\Tests\Framework\ConsoleTestCase;
use Spiral\Tokenizer\ClassesInterface;

final class ValidateCommandTest extends ConsoleTestCase
{
    public function testAllListenersValid(): void
    {
        $mock = $this->mockContainer(ClassesInterface::class);
        $mock->shouldReceive('getClasses')->once()->andReturn([]);

        $this->assertConsoleCommandOutputContainsStrings('tokenizer:validate', strings: [
            'All listeners are correctly configured.'
        ]);
    }

    public function testWithInvalidListener(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('tokenizer:validate', strings: [
            InvalidListener::class,
            'Add #[TargetClass] or #[TargetAttribute] attribute to the listener'
        ]);
    }
}
