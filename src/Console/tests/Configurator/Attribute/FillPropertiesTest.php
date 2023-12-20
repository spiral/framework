<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Configurator\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;
use Spiral\Console\Configurator\Attribute\Parser;
use Spiral\Tests\Console\Fixtures\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class FillPropertiesTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser((new Factory())->create());
    }

    public function testFillArgumentProperties(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->exactly(5))
            ->method('hasArgument')
            ->willReturn(true);

        $input
            ->expects($this->exactly(5))
            ->method('getArgument')
            ->willReturnOnConsecutiveCalls(5, 'foo', ['foo', 'bar'], 0.5, true);

        $command = new #[AsCommand('foo')] class extends Command {
            #[Argument]
            public int $intVal;

            #[Argument]
            public string $strVal;

            #[Argument]
            public array $arrayVal;

            #[Argument]
            public float $floatVal;

            #[Argument]
            public bool $boolVal;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertSame(5, $command->intVal);
        $this->assertSame('foo', $command->strVal);
        $this->assertSame(['foo', 'bar'], $command->arrayVal);
        $this->assertSame(0.5, $command->floatVal);
        $this->assertTrue($command->boolVal);
    }

    public function testSkipPropertyIfArgumentNotPassed(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->once())
            ->method('hasArgument')
            ->with('arg')
            ->willReturn(false);

        $input
            ->expects($this->never())
            ->method('getArgument');

        $command = new #[AsCommand('foo')] class extends Command {
            #[Argument]
            public int $arg;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertFalse((new \ReflectionProperty($command, 'arg'))->isInitialized($command));
    }

    public function testFillOptionProperties(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->exactly(6))
            ->method('hasOption')
            ->willReturn(true);

        $input
            ->expects($this->exactly(6))
            ->method('getOption')
            ->willReturnOnConsecutiveCalls(5, 'foo', ['foo', 'bar'], 0.5, true, true);

        $command = new #[AsCommand('foo')] class extends Command {
            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public int $intVal;

            #[Option(mode: InputOption::VALUE_OPTIONAL)]
            public string $strVal = 'baz';

            #[Option(mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)]
            public array $arrayVal;

            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public float $floatVal;

            #[Option(mode: InputOption::VALUE_NEGATABLE)]
            public bool $boolVal;

            #[Option(mode: InputOption::VALUE_NONE)]
            public bool $otherBoolVal = false;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertSame(5, $command->intVal);
        $this->assertSame('foo', $command->strVal);
        $this->assertSame(['foo', 'bar'], $command->arrayVal);
        $this->assertSame(0.5, $command->floatVal);
        $this->assertTrue($command->boolVal);
        $this->assertTrue($command->otherBoolVal);
    }

    public function testEnumOption(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->exactly(3))
            ->method('hasOption')
            ->willReturn(true);

        $input
            ->expects($this->exactly(3))
            ->method('getOption')
            ->willReturnOnConsecutiveCalls(1, null, 0);

        $command = new #[AsCommand('foo')] class extends Command {
            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public Status $required;

            #[Option(mode: InputOption::VALUE_OPTIONAL)]
            public ?Status $nullable = null;

            #[Option(mode: InputOption::VALUE_OPTIONAL)]
            public ?Status $nullableFilled = null;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertEquals(Status::Active, $command->required);
        $this->assertNull($command->nullable);
        $this->assertEquals(Status::Inactive, $command->nullableFilled);
    }

    public function testSkipPropertyIfOptionNotDefined(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with('option')
            ->willReturn(false);

        $input
            ->expects($this->never())
            ->method('getOption');

        $command = new #[AsCommand('foo')] class extends Command {
            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public int $option;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertFalse((new \ReflectionProperty($command, 'option'))->isInitialized($command));
    }

    public function testSkipPropertyIfOptionNotPassed(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with('option')
            ->willReturn(true);

        $input
            ->expects($this->once())
            ->method('getOption')
            ->willReturn(null);

        $command = new #[AsCommand('foo')] class extends Command {
            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public int $option;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertFalse((new \ReflectionProperty($command, 'option'))->isInitialized($command));
    }

    public function testNullShouldPassWithoutTypecasting(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->once())
            ->method('hasOption')
            ->willReturn(true);

        $input
            ->expects($this->once())
            ->method('getOption')
            ->willReturn(null);

        $command = new #[AsCommand('foo')] class extends Command {
            #[Option(mode: InputOption::VALUE_REQUIRED)]
            public ?int $intVal;
        };

        $this->parser->fillProperties($command, $input);

        $this->assertNull($command->intVal);
    }
}
