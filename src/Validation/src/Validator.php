<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\ValidationException;

final class Validator implements ValidatorInterface
{
    /** @var RulesInterface */
    private $provider;

    /** @var array|\ArrayAccess */
    private $data;

    /** @var array */
    private $errors;

    /** @var mixed */
    private $context;

    /** @var array */
    private $rules;

    /**
     * @param array|\ArrayAccess $data
     * @param array              $rules
     * @param mixed              $context
     * @param RulesInterface     $ruleProvider
     */
    public function __construct($data, array $rules, $context, RulesInterface $ruleProvider)
    {
        $this->data = $data;
        $this->errors = [];
        $this->rules = $rules;
        $this->context = $context;
        $this->provider = $ruleProvider;
    }

    /**
     * Destruct the service.
     */
    public function __destruct()
    {
        $this->data = null;
        $this->rules = [];
        $this->provider = null;
        $this->errors = [];
    }

    /**
     * @inheritdoc
     */
    public function withData($data): ValidatorInterface
    {
        $validator = clone $this;
        $validator->data = $data;
        $validator->errors = [];

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

    /**
     * @inheritdoc
     */
    public function withContext($context): ValidatorInterface
    {
        $validator = clone $this;
        $validator->context = $context;
        $validator->errors = [];

        return $validator;
    }

    /**
     * @inheritdoc
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->getErrors() === [];
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        $this->validate();

        return $this->errors;
    }

    /**
     * Check if value has any error associated.
     *
     * @param string $field
     *
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->getErrors()[$field]);
    }

    /**
     * Validate data over given rules and context.
     *
     * @throws ValidationException
     */
    protected function validate(): void
    {
        if ($this->errors !== []) {
            // already validated
            return;
        }

        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $hasValue = $this->hasValue($field);
            $value = $this->getValue($field);

            foreach ($this->provider->getRules($rules) as $rule) {
                if (!$hasValue && $rule->ignoreEmpty($value) && !$rule->hasConditions()) {
                    continue;
                }

                foreach ($rule->getConditions() as $condition) {
                    if (!$condition->isMet($this, $field, $value)) {
                        // condition is not met, skipping validation
                        continue 2;
                    }
                }

                if (!$rule->validate($this, $field, $value)) {
                    // got error, jump to next field
                    $this->errors[$field] = $rule->getMessage($field, $value);
                    break;
                }
            }
        }
    }
}
