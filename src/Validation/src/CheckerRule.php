<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Translator\Traits\TranslatorTrait;

final class CheckerRule extends AbstractRule
{
    use TranslatorTrait;

    public function __construct(
        private readonly CheckerInterface $checker,
        private readonly string $method,
        private readonly array $args = [],
        private readonly ?string $message = null
    ) {
    }

    public function ignoreEmpty(mixed $value): bool
    {
        return $this->checker->ignoreEmpty($this->method, $value, $this->args);
    }

    public function validate(ValidatorInterface $v, string $field, mixed $value): bool
    {
        return $this->checker->check($v, $this->method, $field, $value, $this->args);
    }

    public function getMessage(string $field, mixed $value): string
    {
        if (!empty($this->message)) {
            return $this->say(
                $this->message,
                \array_merge([$value, $field], $this->args)
            );
        }

        return $this->checker->getMessage($this->method, $field, $value, $this->args);
    }
}
