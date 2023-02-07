<?php

declare(strict_types=1);

namespace Spiral\Console\Interceptor;

use Spiral\Attributes\ReaderInterface;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;
use Spiral\Console\Exception\ConsoleException;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PromptArgumentsInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @param array{
     *     input: InputInterface,
     *     output: OutputInterface,
     *     command: Command
     * } $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): int
    {
        foreach ($parameters['command']->getDefinition()->getArguments() as $argument) {
            if ($argument->isRequired() && $parameters['input']->getArgument($argument->getName()) === null) {
                $parameters['command']->ask($this->getQuestion($parameters['command'], $argument));
            }
        }

        return $core->callAction($controller, $action, $parameters);
    }

    private function getQuestion(Command $command, InputArgument $argument): string
    {
        $reflection = new \ReflectionClass($command);

        /** @var Question $question */
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

        return \sprintf('Please provide a value for the `%s` argument.', $argument->getName());
    }
}
