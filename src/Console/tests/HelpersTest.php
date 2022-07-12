<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\HelperCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class HelpersTest extends BaseTest
{
    private \Spiral\Console\Console $core;

    public function setUp(): void
    {
        parent::setUp();

        $this->core = $this->getCore($this->getStaticLocator([
            HelperCommand::class
        ]));
    }

    public function testVerbose(): void
    {
        $actual = $this->core->run('helper', ['helper' => 'verbose'])
            ->getOutput()
            ->fetch();
        
        $this->assertSame('false', $actual);

        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $actual = $this->core->run('helper', ['helper' => 'verbose', '-v' => true], $output)
            ->getOutput()
            ->fetch();

        $this->assertSame('true', $actual);
    }

    public function testSprintf(): void
    {
        $this->assertStringContainsString(
            'hello world',
            $this->core->run('helper', ['helper' => 'sprintf'])->getOutput()->fetch()
        );
    }

    public function testWriteln(): void
    {
        $this->assertStringContainsString(
            "\n",
            $this->core->run('helper', ['helper' => 'writeln'])->getOutput()->fetch()
        );
    }

    public function testTable(): void
    {
        $this->assertStringContainsString(
            'id',
            $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            'value',
            $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            '1',
            $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            'true',
            $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );
    }
}
