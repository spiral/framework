<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Core\Attribute\Singleton;
use Spiral\Core\InvokerInterface;
use Spiral\Validation\Exception\ValidationException;

#[Singleton]
final class ValidationProvider implements ValidationProviderInterface
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
