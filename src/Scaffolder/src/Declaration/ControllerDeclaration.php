<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

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
    /** @var bool */
    private $withPrototype = false;

    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, '', [], $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->withPrototype ? [PrototypeTrait::class => null] : [];
    }

    public function addAction(string $action): Method
    {
        $method = $this->method($action);

        return $method->setPublic();
    }

    public function addPrototypeTrait(): void
    {
        $this->withPrototype = true;

        $this->addTrait('PrototypeTrait');
    }
}
