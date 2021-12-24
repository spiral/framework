<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

final class Validator extends AbstractValidator
{
    /** @var array|\ArrayAccess */
    private $data;

    /**
     * @param array|\ArrayAccess $data
     * @param mixed              $context
     * @param RulesInterface     $ruleProvider
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
     * @inheritdoc
     */
    public function withData($data): ValidatorInterface
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
