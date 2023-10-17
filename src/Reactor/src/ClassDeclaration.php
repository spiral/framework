<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\ClassType;
use Spiral\Reactor\Partial\Constant;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\Property;
use Spiral\Reactor\Partial\TraitUse;
use Spiral\Reactor\Traits;

/**
 * @extends AbstractDeclaration<ClassType>
 */
class ClassDeclaration extends AbstractDeclaration implements AggregableInterface
{
    use Traits\ConstantsAware;
    use Traits\MethodsAware;
    use Traits\PropertiesAware;
    use Traits\TraitsAware;

    public function __construct(?string $name = null)
    {
        $this->element = new ClassType($name);
    }

    public function setFinal(bool $state = true): static
    {
        $this->element->setFinal($state);

        return $this;
    }

    public function isFinal(): bool
    {
        return $this->element->isFinal();
    }

    public function setAbstract(bool $state = true): static
    {
        $this->element->setAbstract($state);

        return $this;
    }

    public function isAbstract(): bool
    {
        return $this->element->isAbstract();
    }

    public function setExtends(?string $name): static
    {
        $this->element->setExtends($name);

        return $this;
    }

    public function getExtends(): ?string
    {
        return $this->element->getExtends();
    }

    /**
     * @param string[] $names
     */
    public function setImplements(array $names): static
    {
        $this->element->setImplements($names);

        return $this;
    }

    /** @return string[] */
    public function getImplements(): array
    {
        return $this->element->getImplements();
    }

    public function addImplement(string $name): static
    {
        $this->element->addImplement($name);

        return $this;
    }

    public function removeImplement(string $name): static
    {
        $this->element->removeImplement($name);

        return $this;
    }

    public function addMember(Method|Property|Constant|TraitUse $member): static
    {
        $this->element->addMember($member->getElement());

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(ClassType $element): static
    {
        $class = new static();

        $class->element = $element;

        return $class;
    }

    /**
     * @internal
     */
    public function getElement(): ClassType
    {
        return $this->element;
    }
}
