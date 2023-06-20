<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\InjectionException;

/**
 * Manages automatic container injections of class and invocation locators.
 *
 * @implements InjectorInterface<InterfaceLocator>
 */
final class InterfaceLocatorInjector implements InjectorInterface
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
    ): InterfacesInterface {
        return $this->tokenizer->interfaceLocator();
    }
}
