<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var callable */
    private $check;

    /** @var array */
    private $args;

    /** @var string|null */
    private $message;

    public function __construct(callable $check, array $args = [], ?string $message = null)
    {
        $this->check = $check;
        $this->args = $args;
        $this->message = $message;
    }

    /**
     * @inheritdoc
     *
     * Attention: callable conditions are required for non empty values only.
     */
    public function ignoreEmpty($value): bool
    {
        return empty($value);
    }

    /**
     * @inheritdoc
     */
    public function validate(ValidatorInterface $v, string $field, $value): bool
    {
        $args = $this->args;
        array_unshift($args, $value);

        return call_user_func_array($this->check, $args);
    }

    /**
     * @inheritdoc
     */
    public function getMessage(string $field, $value): string
    {
        if (!empty($this->message)) {
            return Translator::interpolate(
                $this->message,
                array_merge([$value, $field], $this->args)
            );
        }

        $name = $this->check;
        if (is_array($name) && isset($name[0], $name[1])) {
            $name = sprintf(
                '%s::%s',
                is_object($name[0]) ? get_class($name[0]) : $name,
                $name[1]
            );
        } elseif (!is_string($name)) {
            $name = '~user-defined~';
        }

        return $this->say(static::DEFAULT_MESSAGE, ['name' => $name]);
    }
}
