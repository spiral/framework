<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\ParserException;

/**
 * Parses rule definitions.
 */
final class RuleParser implements ParserInterface
{
    public const ARGUMENTS  = ['args', 'params', 'arguments', 'parameters'];
    public const MESSAGES   = ['message', 'msg', 'error', 'err'];
    public const CONDITIONS = ['if', 'condition', 'conditions', 'where', 'when'];

    public function split(mixed $rules): \Generator
    {
        $rules = \is_array($rules) ? $rules : [$rules];

        foreach ($rules as $rule) {
            if ($rule instanceof \Closure) {
                yield null => $rule;
                continue;
            }

            yield $this->getID($rule) => $rule;
        }
    }

    public function parseCheck(mixed $chunk): array|callable|string
    {
        if (\is_string($chunk)) {
            $function = \str_replace('::', ':', $chunk);
        } else {
            if (!\is_array($chunk) || !isset($chunk[0])) {
                throw new ParserException('Validation rule does not define any check.');
            }

            $function = $chunk[0];
        }

        if (\is_string($function)) {
            return \str_replace('::', ':', $function);
        }

        return $function;
    }

    public function parseArgs(mixed $chunk): array
    {
        if (!\is_array($chunk)) {
            return [];
        }

        foreach (self::ARGUMENTS as $index) {
            if (isset($chunk[$index])) {
                return $chunk[$index];
            }
        }

        unset($chunk[0]);
        foreach (\array_merge(self::MESSAGES, self::CONDITIONS) as $index) {
            unset($chunk[$index]);
        }

        return $chunk;
    }

    public function parseMessage(mixed $chunk): ?string
    {
        if (!\is_array($chunk)) {
            return null;
        }

        foreach (self::MESSAGES as $index) {
            if (isset($chunk[$index])) {
                return $chunk[$index];
            }
        }

        return null;
    }

    public function parseConditions(mixed $chunk): array
    {
        foreach (self::CONDITIONS as $index) {
            if (isset($chunk[$index])) {
                $conditions = [];
                foreach ((array)$chunk[$index] as $key => $value) {
                    if (\is_numeric($key)) {
                        $conditions[$value] = [];
                    } else {
                        $conditions[$key] = (array)$value;
                    }
                }

                return $conditions;
            }
        }

        return [];
    }

    protected function getID(mixed $rule): string
    {
        return json_encode($rule);
    }
}
