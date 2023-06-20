<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\InjectionException;

/**
 * Manages automatic container injections of class and invocation locators.
 *
 * @implements InjectorInterface<EnumLocator>
 */
final class EnumLocatorInjector implements InjectorInterface
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
    ): EnumsInterface {
        return $this->tokenizer->enumLocator();
    }
}
