<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\InjectionException;

/**
 * Manages automatic container injections of class and invocation locators.
 *
 * @implements InjectorInterface<InvocationLocator>
 */
final class InvocationLocatorInjector implements InjectorInterface
{
    public function __construct(
        private readonly Tokenizer $tokenizer
    ) {
    }

    /**
     * @throws InjectionException
     */
    public function createInjection(
        \ReflectionClass $class,
        string $context = null
    ): InvocationsInterface {
        return $this->tokenizer->invocationLocator();
    }
}
