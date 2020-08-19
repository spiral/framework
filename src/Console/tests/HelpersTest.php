<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\StaticLocator;
use Spiral\Tests\Console\Fixtures\HelperCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class HelpersTest extends BaseTest
{
    public function testVerbose(): void
    {
        $core = $this->getCore(new StaticLocator([
            HelperCommand::class
        ]));

        $actual = $core->run('helper', ['helper' => 'verbose'])
            ->getOutput()
            ->fetch();
        $this->assertSame('false', $actual);

        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $actual = $core->run('helper', ['helper' => 'verbose', '-v' => true], $output)
            ->getOutput()
            ->fetch();

        $this->assertSame('true', $actual);
    }

    public function testSprinf(): void
    {
        $core = $this->getCore(new StaticLocator([
            HelperCommand::class
        ]));

        $this->assertStringContainsString(
            'hello world',
            $core->run('helper', ['helper' => 'sprintf'])->getOutput()->fetch()
        );
    }

    public function testWriteln(): void
    {
        $core = $this->getCore(new StaticLocator([
            HelperCommand::class
        ]));

        $this->assertStringContainsString(
            "\n",
            $core->run('helper', ['helper' => 'writeln'])->getOutput()->fetch()
        );
    }

    public function testTable(): void
    {
        $core = $this->getCore(new StaticLocator([
            HelperCommand::class
        ]));

        $this->assertStringContainsString(
            'id',
            $core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            'value',
            $core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            '1',
            $core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );

        $this->assertStringContainsString(
            'true',
            $core->run('helper', ['helper' => 'table'])->getOutput()->fetch()
        );
    }
}
