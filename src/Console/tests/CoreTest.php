<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\TestCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CoreTest extends BaseTestCase
{
    public function testWelcome(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            TestCommand::class
        ]));

        $this->assertSame(
            'Hello World - 0',
            $core->run('test')->getOutput()->fetch()
        );

        $this->assertSame(
            'Hello World - 1',
            $core->run('test')->getOutput()->fetch()
        );
    }

    public function testStart(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            TestCommand::class
        ]));

        $output = new BufferedOutput();

        $core->start(new ArrayInput([]), $output);
        $output = $output->fetch();

        $this->assertStringContainsString('Spiral Framework', $output);
        $this->assertStringContainsString('Test Command', $output);
        $this->assertStringContainsString('test:user', $output);
    }
}
