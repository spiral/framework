<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;
use Spiral\Console\Exception\ConsoleException;
use Spiral\Console\PromptArguments;
use Spiral\Tests\Console\Fixtures\Attribute\WithNameCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PromptArgumentsTest extends BaseTestCase
{
    public function testCommandArgumentShouldBeSkipped(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->expects($this->never())
            ->method('getArgument');
        $input
            ->expects($this->never())
            ->method('setArgument');

        $promptArguments = new PromptArguments();
        $command = new WithNameCommand();
        $command->setDefinition(new InputDefinition([new InputArgument('command', InputArgument::REQUIRED)]));


        $promptArguments->promptMissedArguments($command, $input, $this->createMock(OutputInterface::class));
    }

    public function testPromptArgumentWithDefaultQuestion(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->assertSame('Please provide a value for the `email` argument', $method->invoke(
            $promptArguments,
            new WithNameCommand(),
            new InputArgument('email', InputArgument::REQUIRED))
        );
    }

    public function testPromptArgumentWithQuestionOnClass(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->assertSame('This is question from the attribute', $method->invoke(
            $promptArguments,
            new #[
                AsCommand(name: 'foo'),
                Question(question: 'This is question from the attribute', argument: 'email')
            ] class extends Command
            {
                public function perform(): int
                {
                    return self::SUCCESS;
                }
            },
            new InputArgument('email', InputArgument::REQUIRED))
        );
    }

    public function testPromptArgumentWithQuestionOnClassWithWrongArgumentShouldBeSkipped(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->assertSame('Please provide a value for the `email` argument', $method->invoke(
            $promptArguments,
            new #[
                AsCommand(name: 'foo'),
                Question(question: 'This is question from the attribute', argument: 'foo')
            ] class extends Command
            {
                public function perform(): int
                {
                    return self::SUCCESS;
                }
            },
            new InputArgument('email', InputArgument::REQUIRED))
        );
    }

    public function testPromptArgumentWithQuestionOnProperty(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->assertSame('This is question from the attribute on the property', $method->invoke(
            $promptArguments,
            new #[AsCommand(name: 'foo')] class extends Command
            {
                #[Question(question: 'This is question from the attribute on the property')]
                private readonly string $email;

                public function perform(): int
                {
                    return self::SUCCESS;
                }
            },
            new InputArgument('email', InputArgument::REQUIRED))
        );
    }

    public function testPromptArgumentWithQuestionOnDifferentPropertyShouldBeSkipped(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->assertSame('Please provide a value for the `email` argument', $method->invoke(
            $promptArguments,
            new #[AsCommand(name: 'foo')] class extends Command
            {
                private readonly string $email;

                #[Question(question: 'This is question from the attribute on the property')]
                private readonly string $password;

                public function perform(): int
                {
                    return self::SUCCESS;
                }
            },
            new InputArgument('email', InputArgument::REQUIRED))
        );
    }

    public function testPromptArgumentException(): void
    {
        $promptArguments = new PromptArguments();
        $method = (new \ReflectionClass($promptArguments))->getMethod('getQuestion');

        $this->expectException(ConsoleException::class);
        $method->invoke(
            $promptArguments,
            new #[
                AsCommand(name: 'foo'),
                Question(question: 'Bar')
            ] class extends Command
            {
                public function perform(): int
                {
                    return self::SUCCESS;
                }
            },
            new InputArgument('email', InputArgument::REQUIRED)
        );
    }
}
