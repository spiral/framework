<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Nette\PhpGenerator\ClassLike;
use Spiral\Reactor\Traits;

/**
 * Generic element declaration.
 *
 * @template T of ClassLike
 */
abstract class AbstractDeclaration implements DeclarationInterface, NamedInterface, \Stringable
{
    use Traits\CommentAware;
    use Traits\NameAware;
    use Traits\AttributeAware;

    /**
     * @var T
     */
    protected ClassLike $element;

    public function __toString(): string
    {
        return $this->element->__toString();
    }

    public function setName(?string $name): self
    {
        if ($name !== null) {
            $name = (new InflectorFactory())->build()->classify($name);
        }

        $this->element->setName($name);

        return $this;
    }

    public function isClass(): bool
    {
        return $this instanceof ClassDeclaration;
    }

    public function isInterface(): bool
    {
        return $this instanceof InterfaceDeclaration;
    }

    public function isTrait(): bool
    {
        return $this instanceof TraitDeclaration;
    }

    public function isEnum(): bool
    {
        return $this instanceof EnumDeclaration;
    }

    public function render(): string
    {
        return $this->__toString();
    }
}
