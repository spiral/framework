<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Validation\Exception\ValidationException;

final class ValidationProvider implements ValidationProviderInterface, SingletonInterface
{
    /** @var array<non-empty-string, \Closure> */
    private array $resolvers = [];

    public function __construct(
        private readonly InvokerInterface $invoker
    ) {
    }

    /**
     * @param non-empty-string $name
     */
    public function register(string $name, \Closure $resolver): void
    {
        $this->resolvers[$name] = $resolver;
    }

    /**
     * @param non-empty-string $name
     */
    public function getValidation(string $name, array $params = []): ValidationInterface
    {
        if (!isset($this->resolvers[$name])) {
            throw new ValidationException(\sprintf('Validation with name `%s` is not registered.', $name));
        }

        return $this->invoker->invoke(
            $this->resolvers[$name],
            $params
        );
    }
}
