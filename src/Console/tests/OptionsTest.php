<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\OptionalCommand;

class OptionsTest extends BaseTest
{
    public function testOptions(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            OptionalCommand::class
        ]));

        $this->assertSame(
            'no option',
            $core->run(command: 'optional')->getOutput()->fetch()
        );

        $this->assertSame(
            'hello',
            $core->run(command: 'optional', input: ['-o' => true, 'arg' => 'hello'])->getOutput()->fetch()
        );

        $this->assertSame(
            0,
            $core->run(command: 'optional', input: ['-o' => true, 'arg' => 'hello'])->getCode()
        );
    }
}
