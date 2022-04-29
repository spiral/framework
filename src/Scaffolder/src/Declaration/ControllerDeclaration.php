<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;
use Spiral\Reactor\Partial\Method;

/**
 * Declares controller.
 */
class ControllerDeclaration extends ClassDeclaration implements DependedInterface
{
    private bool $withPrototype = false;

    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, '', [], $comment);
    }

    public function getDependencies(): array
    {
        return $this->withPrototype ? [PrototypeTrait::class => null] : [];
    }

    public function addAction(string $action): Method
    {
        return $this->method($action)->setPublic();
    }

    public function addPrototypeTrait(): void
    {
        $this->withPrototype = true;

        $this->addTrait('PrototypeTrait');
    }
}
