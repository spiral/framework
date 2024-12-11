<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\StringInput;

final class SignatureTest extends BaseTestCase
{
    public function testOptions(): void
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                new class extends Command {
                    protected const SIGNATURE = 'foo:bar {arg?} {--o|option}';

                    public function perform(): int
                    {
                        $argument = $this->argument('arg');
                        $option = $this->option('option');

                        if ($argument) {
                            $this->write('argument : '.$argument);
                        }

                        if ($option) {
                            $this->write('option : '.$option);
                        }

                        return 1;
                    }
                },
            ])
        );

        $this->assertSame(
            '',
            $core->run(command: 'foo:bar')->getOutput()->fetch()
        );

        $this->assertSame(
            'argument : baz',
            $core->run(command: 'foo:bar', input: ['arg' => 'baz'])->getOutput()->fetch()
        );

        $this->assertSame(
            'option : baz',
            $core->run(command: 'foo:bar', input: ['-o' => 'baz'])->getOutput()->fetch()
        );

        $this->assertSame(
            'option : baz',
            $core->run(command: 'foo:bar', input: ['--option' => 'baz'])->getOutput()->fetch()
        );

        $this->assertSame(
            'argument : bazoption : baf',
            $core->run(command: 'foo:bar', input: ['arg' => 'baz', '-o' => 'baf'])->getOutput()->fetch()
        );
    }

    public function testArrayableOptions(): void
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                new class extends Command {
                    protected const SIGNATURE = 'foo:bar {arg[]?} {--o|option[]=}';

                    public function perform(): int
                    {
                        $argument = (array)$this->argument('arg');
                        $option = (array)$this->option('option');

                        if ($argument) {
                            $this->write('argument : '.\implode(',', $argument));
                        }

                        if ($option) {
                            $this->write('option : '.\implode(',', $option));
                        }

                        return 1;
                    }
                },
            ])
        );

        $this->assertSame(
            '',
            $core->run(command: 'foo:bar')->getOutput()->fetch()
        );

        $this->assertSame(
            'argument : bar,baz,bak',
            $core->run(command: 'foo:bar', input: new StringInput('foo:bar bar baz bak'))->getOutput()->fetch()
        );

        $this->assertSame(
            'option : far,faz',
            $core->run(command: 'foo:bar', input: new StringInput('foo:bar -ofar --option=faz'))->getOutput()->fetch()
        );

        $this->assertSame(
            'option : baz',
            $core->run(command: 'foo:bar', input: ['--option' => 'baz'])->getOutput()->fetch()
        );

        $this->assertSame(
            'argument : bar,baz,bakoption : far,faz',
            $core->run(command: 'foo:bar', input: new StringInput('foo:bar bar baz bak -ofar --option=faz'))->getOutput(
            )->fetch()
        );
    }

    public function testDescription(): void
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                new class extends Command {
                    protected const SIGNATURE = 'foo:bar
                                    {foo : Foo arg description. }
                                    {bar=default : Bar arg description. }
                                    {baz[]? : Baz arg description. }
                                    {--o|id[]= : Id option description. }
                                    {--Q|quit : Quit option description. }
                                    {--naf=default : Naf option description. }
                    ';

                    public function perform(): int
                    {
                        return 1;
                    }
                },
            ])
        );

        $this->assertStringStartsWith(
            <<<'HELP'
Usage:
  foo:bar [options] [--] <foo> [<bar> [<baz>...]]

Arguments:
  foo                   Foo arg description.
  bar                   Bar arg description. [default: "default"]
  baz                   Baz arg description.

Options:
  -o, --id[=ID]         Id option description. (multiple values allowed)
  -Q, --quit            Quit option description.
      --naf[=NAF]       Naf option description. [default: "default"]
HELP
            ,
            $core->run(command: 'help', input: ['command_name' => 'foo:bar'])->getOutput()->fetch()
        );
    }

    public function testDescriptionFromConstant(): void
    {
        $core = $this->getCore(
            $this->getStaticLocator([
                new class extends Command {
                    protected const SIGNATURE = 'foo:bar';
                    protected const DESCRIPTION = 'baz';

                    public function perform(): int
                    {
                        $this->write($this->getDescription());

                        return self::SUCCESS;
                    }
                },
            ])
        );

        $this->assertSame(
            'baz',
            $core->run(command: 'foo:bar')->getOutput()->fetch()
        );
    }
}
