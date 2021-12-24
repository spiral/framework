<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /**
     * @inheritdoc
     */
    public function split($rules): \Generator
    {
        $rules = is_array($rules) ? $rules : [$rules];

        foreach ($rules as $rule) {
            if ($rule instanceof \Closure) {
                yield null => $rule;
                continue;
            }

            yield $this->getID($rule) => $rule;
        }
    }

    /**
     * @inheritdoc
     */
    public function parseCheck($chunk)
    {
        if (is_string($chunk)) {
            $function = str_replace('::', ':', $chunk);
        } else {
            if (!is_array($chunk) || !isset($chunk[0])) {
                throw new ParserException('Validation rule does not define any check.');
            }

            $function = $chunk[0];
        }

        if (is_string($function)) {
            return str_replace('::', ':', $function);
        }

        return $function;
    }

    /**
     * @inheritdoc
     */
    public function parseArgs($chunk): array
    {
        if (!is_array($chunk)) {
            return [];
        }

        foreach (self::ARGUMENTS as $index) {
            if (isset($chunk[$index])) {
                return $chunk[$index];
            }
        }

        unset($chunk[0]);
        foreach (array_merge(self::MESSAGES, self::CONDITIONS) as $index) {
            unset($chunk[$index]);
        }

        return $chunk;
    }

    /**
     * @inheritdoc
     */
    public function parseMessage($chunk): ?string
    {
        if (!is_array($chunk)) {
            return null;
        }

        foreach (self::MESSAGES as $index) {
            if (isset($chunk[$index])) {
                return $chunk[$index];
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function parseConditions($chunk): array
    {
        foreach (self::CONDITIONS as $index) {
            if (isset($chunk[$index])) {
                $conditions = [];
                foreach ((array)$chunk[$index] as $key => $value) {
                    if (is_numeric($key)) {
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

    /**
     * @param $rule
     */
    protected function getID($rule): string
    {
        return json_encode($rule);
    }
}
