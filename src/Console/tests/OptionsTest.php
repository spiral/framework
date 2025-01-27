<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\OptionalCommand;

class OptionsTest extends BaseTestCase
{
    public function testOptions(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            OptionalCommand::class,
        ]));

        self::assertSame('no option', $core->run(command: 'optional')->getOutput()->fetch());

        self::assertSame('hello', $core->run(command: 'optional', input: ['-o' => true, 'arg' => 'hello'])->getOutput()->fetch());

        self::assertSame(0, $core->run(command: 'optional', input: ['-o' => true, 'arg' => 'hello'])->getCode());
    }
}
