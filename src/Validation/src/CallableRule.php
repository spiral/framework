<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\Translator;

/**
 * Represents options to describe singular validation rule.
 */
final class CallableRule extends AbstractRule
{
    use TranslatorTrait;

    /**
     * Default validation message for custom rules.
     */
    public const DEFAULT_MESSAGE = '[[The condition `{name}` was not met.]]';

    private \Closure|array|string $check;

    public function __construct(
        callable $check,
        private readonly array $args = [],
        private readonly ?string $message = null
    ) {
        $this->check = $check;
    }

    /**
     * Attention: callable conditions are required for non empty values only.
     */
    public function ignoreEmpty(mixed $value): bool
    {
        return empty($value);
    }

    public function validate(ValidatorInterface $v, string $field, mixed $value): bool
    {
        $args = $this->args;
        \array_unshift($args, $value);

        return \call_user_func_array($this->check, $args);
    }

    public function getMessage(string $field, mixed $value): string
    {
        if (!empty($this->message)) {
            return Translator::interpolate(
                $this->message,
                \array_merge([$value, $field], $this->args)
            );
        }

        $name = $this->check;
        if (\is_array($name) && isset($name[0], $name[1])) {
            $name = sprintf(
                '%s::%s',
                \is_object($name[0]) ? $name[0]::class : $name,
                $name[1]
            );
        } elseif (!\is_string($name)) {
            $name = '~user-defined~';
        }

        return $this->say(static::DEFAULT_MESSAGE, ['name' => $name]);
    }
}
