<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

use ArrayAccess;

final class Validator extends AbstractValidator
{
    /** @var null|array|ArrayAccess */
    private $data;

    /**
     * @param null|array|ArrayAccess $data
     * @param mixed              $context
     */
    public function __construct($data, array $rules, $context, RulesInterface $ruleProvider)
    {
        $this->data = $data;
        parent::__construct($rules, $context, $ruleProvider);
    }

    /**
     * Destruct the service.
     */
    public function __destruct()
    {
        $this->data = null;
        parent::__destruct();
    }

    /**
     * @param ArrayAccess|array $data
     */
    public function withData(iterable $data): ValidatorInterface
    {
        $validator = clone $this;
        $validator->data = $data;

        return $validator;
    }

    /**
     * @inheritdoc
     */
    public function getValue(string $field, $default = null)
    {
        $value = $this->data[$field] ?? $default;

        if (is_object($value) && method_exists($value, 'getValue')) {
            return $value->getValue();
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function hasValue(string $field): bool
    {
        if (is_array($this->data)) {
            return array_key_exists($field, $this->data);
        }

        return isset($this->data[$field]);
    }
}
