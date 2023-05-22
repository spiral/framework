<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Config\Binding;
use Spiral\Core\Config\Inflector;
use Spiral\Core\Container\Autowire;

/**
 * @psalm-type TResolver = class-string|non-empty-string|callable|array{class-string, non-empty-string}|Autowire
 *
 * @internal
 */
final class State
{
    /**
     * @var array<string, Binding>
     */
    public array $bindings = [];

    /**
     * @var array<string, mixed> Cache for singletons
     */
    public array $singletons = [];

    /**
     * @var array<class-string, string>
     */
    public array $injectors = [];

    /**
     * @var array<class-string, Inflector[]>
     */
    public array $inflectors = [];

    /**
     * List of finalizers to be called on container scope destruction.
     * @var callable[]
     */
    public array $finalizers = [];

    public function destruct(): void
    {
        $this->singletons = [];
        $this->injectors = [];
        $this->bindings = [];
        $this->finalizers = [];
    }
}
