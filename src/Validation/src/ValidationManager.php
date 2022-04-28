<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\InvokerInterface;

final class ValidationManager implements SingletonInterface
{
    /** @var array<class-string, Closure> */
    private array $validations = [];

    public function __construct(
        private readonly InvokerInterface $invoker
    ) {
    }

    public function register(string $name, \Closure $validation): void
    {
        $this->validations[$name] = $validation;
    }

    public function getValidation(string $name, array $params = []): ValidationInterface
    {
        if (!isset($this->validations[$name])) {
            throw new \RuntimeException("Validation with name `{$name}` is not registered.");
        }

        return $this->invoker->invoke(
            $this->validations[$name],
            $params
        );
    }
}
