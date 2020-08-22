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

/**
 * @inherit-messages
 */
abstract class AbstractChecker implements CheckerInterface
{
    use TranslatorTrait;

    /** Error messages associated with checker method by name. */
    public const MESSAGES = [];

    /** Default error message if no other messages are found. */
    public const DEFAULT_MESSAGE = '[[The condition `{method}` was not met.]]';

    /** List of methods which are allowed to handle empty values. */
    public const ALLOW_EMPTY_VALUES = [];

    /** @var ValidatorInterface */
    private $validator;

    /**
     * {@inheritdoc}
     */
    public function ignoreEmpty(string $method, $value, array $args): bool
    {
        if (!empty($value)) {
            return false;
        }

        return !in_array($method, static::ALLOW_EMPTY_VALUES, true);
    }

    /**
     * {@inheritdoc}
     */
    public function check(
        ValidatorInterface $v,
        string $method,
        string $field,
        $value,
        array $args = []
    ): bool {
        try {
            $this->validator = $v;
            array_unshift($args, $value);

            return call_user_func_array([$this, $method], $args);
        } finally {
            $this->validator = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(string $method, string $field, $value, array $arguments = []): string
    {
        $messages = static::MESSAGES;
        if (isset($messages[$method])) {
            array_unshift($arguments, $field);

            return $this->say(static::MESSAGES[$method], $arguments);
        }

        return $this->say(static::DEFAULT_MESSAGE, compact('method'));
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
