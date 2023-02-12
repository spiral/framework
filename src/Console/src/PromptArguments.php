<?php

declare(strict_types=1);

namespace Spiral\Console;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Exception\ConsoleException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class PromptArguments
{
    public function __construct(
        private readonly ReaderInterface $reader = new AttributeReader()
    ) {
    }

    public function promptMissedArguments(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($command->getDefinition()->getArguments() as $argument) {
            // Skip default argument "the command to execute"
            if ($argument->getName() === 'command') {
                continue;
            }

            if ($argument->isRequired() && $input->getArgument($argument->getName()) === null) {
                $input->setArgument(
                    $argument->getName(),
                    $io->ask($this->getQuestion($command, $argument))
                );
            }
        }
    }

    private function getQuestion(Command $command, InputArgument $argument): string
    {
        $reflection = new \ReflectionClass($command);

        foreach ($this->reader->getClassMetadata($reflection, Question::class) as $question) {
            if ($question->argument === null) {
                throw new ConsoleException(
                    'When using a `Question` attribute on a console command class, the argument parameter is required.'
                );
            }

            if ($argument->getName() === $question->argument) {
                return $question->question;
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $question = $this->reader->firstPropertyMetadata($property, Question::class);
            if ($question === null) {
                continue;
            }

            if ($argument->getName() === ($question->argument ?? $property->getName())) {
                return $question->question;
            }
        }

        return \sprintf('Please provide a value for the `%s` argument', $argument->getName());
    }
}
