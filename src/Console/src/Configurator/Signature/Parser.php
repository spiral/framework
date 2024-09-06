<?php

declare(strict_types=1);

namespace Spiral\Console\Configurator\Signature;

use InvalidArgumentException;
use Spiral\Console\Configurator\CommandDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Console signature parser.
 * @internal
 */
final class Parser
{
    /**
     * Parse the given console command definition into an array.
     *
     * @throws InvalidArgumentException
     */
    public function parse(string $signature): CommandDefinition
    {
        $name = $this->parseName($signature);

        if (\preg_match_all('/\{\s*(.*?)\s*\}/', $signature, $matches)) {
            if (\count($matches[1])) {
                return new CommandDefinition($name, ...$this->parseParameters($matches[1]));
            }
        }

        return new CommandDefinition($name);
    }

    /**
     * Extract the name of the command from the expression.
     *
     * @throws InvalidArgumentException
     */
    private function parseName(string $signature): string
    {
        if (!\preg_match('/\S+/', $signature, $matches)) {
            throw new InvalidArgumentException('Unable to determine command name from signature.');
        }

        return $matches[0];
    }

    /**
     * Extract all the parameters from the tokens.
     *
     * @return array{0: InputArgument[], 1: InputOption[]}
     */
    private function parseParameters(array $tokens): array
    {
        $arguments = [];
        $options = [];

        foreach ($tokens as $token) {
            if (\preg_match('/-{2,}(.*)/', (string) $token, $matches)) {
                $options[] = $this->parseOption($matches[1]);
            } else {
                $arguments[] = $this->parseArgument($token);
            }
        }

        return [$arguments, $options];
    }

    /**
     * Parse an argument expression.
     */
    private function parseArgument(string $token): InputArgument
    {
        $matches = [];
        [$token, $description] = $this->extractDescription($token);

        return match (true) {
            \str_ends_with($token, '[]?') => new InputArgument(
                \rtrim($token, '[]?'),
                InputArgument::IS_ARRAY,
                $description
            ),
            \str_ends_with($token, '[]') => new InputArgument(
                \rtrim($token, '[]'),
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                $description
            ),
            \str_ends_with($token, '?') => new InputArgument(
                \rtrim($token, '?'),
                InputArgument::OPTIONAL,
                $description
            ),
            (bool)\preg_match('/(.+)\[\]\=(.+)/', $token, $matches) => new InputArgument(
                $matches[1],
                InputArgument::IS_ARRAY,
                $description,
                \preg_split('/,\s?/', $matches[2])
            ),
            (bool)\preg_match('/(.+)\=(.+)?/', $token, $matches) => new InputArgument(
                $matches[1],
                InputArgument::OPTIONAL,
                $description,
                $matches[2] ?? null
            ),
            default => new InputArgument(
                $token,
                InputArgument::REQUIRED,
                $description
            ),
        };
    }

    /**
     * Parse an option expression.
     */
    private function parseOption(string $token): InputOption
    {
        [$token, $description] = $this->extractDescription($token);

        $matches = \preg_split('/\s*\|\s*/', $token, 2);
        $shortcut = null;

        if (isset($matches[1])) {
            [$shortcut, $token] = $matches;
        }

        return match (true) {
            \str_ends_with($token, '[]=') => new InputOption(
                \rtrim($token, '[]='),
                $shortcut,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                $description
            ),
            \str_ends_with($token, '=') => new InputOption(
                \rtrim($token, '='),
                $shortcut,
                InputOption::VALUE_OPTIONAL,
                $description
            ),
            (bool)\preg_match('/(.+)\[\]\=(.+)/', $token, $matches) => new InputOption(
                $matches[1],
                $shortcut,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                $description,
                \preg_split('/,\s?/', $matches[2])
            ),
            (bool)\preg_match('/(.+)\=(.+)/', $token, $matches) => new InputOption(
                $matches[1],
                $shortcut,
                InputOption::VALUE_OPTIONAL,
                $description,
                $matches[2]
            ),
            default => new InputOption(
                $token,
                $shortcut,
                InputOption::VALUE_NONE,
                $description
            ),
        };
    }

    /**
     * Parse the token into its token and description segments.
     *
     * @return array{
     *     0: non-empty-string,
     *     1: string
     * }
     */
    private function extractDescription(string $token): array
    {
        $token = \trim($token);

        $parts = \array_map('trim', \explode(':', $token, 2));

        return \count($parts) === 2 ? $parts : [$token, ''];
    }
}
