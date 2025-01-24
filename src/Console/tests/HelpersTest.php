<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\HelperCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class HelpersTest extends BaseTestCase
{
    private \Spiral\Console\Console $core;

    public function testVerbose(): void
    {
        $actual = $this->core->run('helper', ['helper' => 'verbose'])
            ->getOutput()
            ->fetch();

        self::assertSame('false', $actual);

        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $actual = $this->core->run('helper', ['helper' => 'verbose', '-v' => true], $output)
            ->getOutput()
            ->fetch();

        self::assertSame('true', $actual);
    }

    public function testSprintf(): void
    {
        self::assertStringContainsString('hello world', $this->core->run('helper', ['helper' => 'sprintf'])->getOutput()->fetch());
    }

    public function testWriteln(): void
    {
        self::assertStringContainsString("\n", $this->core->run('helper', ['helper' => 'writeln'])->getOutput()->fetch());
    }

    public function testTable(): void
    {
        self::assertStringContainsString('id', $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch());

        self::assertStringContainsString('value', $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch());

        self::assertStringContainsString('1', $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch());

        self::assertStringContainsString('true', $this->core->run('helper', ['helper' => 'table'])->getOutput()->fetch());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->core = $this->getCore($this->getStaticLocator([
            HelperCommand::class,
        ]));
    }
}
