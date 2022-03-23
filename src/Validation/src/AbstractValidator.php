<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\ValidationException;

abstract class AbstractValidator implements ValidatorInterface
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private array $rules,
        private mixed $context,
        private RulesInterface $provider
    ) {
    }

    /**
     * Destruct the service.
     */
    public function __destruct()
    {
        $this->rules = [];
        unset($this->provider);
        $this->errors = [];
    }

    public function __clone()
    {
        $this->errors = [];
    }

    public function withContext(mixed $context): ValidatorInterface
    {
        $validator = clone $this;
        $validator->context = $context;

        return $validator;
    }

    public function getContext(): mixed
    {
        return $this->context;
    }

    public function isValid(): bool
    {
        return $this->getErrors() === [];
    }

    public function getErrors(): array
    {
        $this->validate();

        return $this->errors;
    }

    /**
     * Check if value has any error associated.
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
    final protected function validate(): void
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
