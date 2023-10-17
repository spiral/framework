<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\InterfaceType;
use Spiral\Reactor\Partial\Constant;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Traits;

/**
 * @extends AbstractDeclaration<InterfaceType>
 */
class InterfaceDeclaration extends AbstractDeclaration implements AggregableInterface
{
    use Traits\ConstantsAware;
    use Traits\MethodsAware;

    public function __construct(string $name)
    {
        $this->element = new InterfaceType($name);
    }

    public function setExtends(string|array $names): static
    {
        $this->element->setExtends($names);

        return $this;
    }

    /** @return string[] */
    public function getExtends(): array
    {
        return $this->element->getExtends();
    }

    public function addExtend(string $name): static
    {
        $this->element->addExtend($name);

        return $this;
    }

    public function addMember(Method|Constant $member): static
    {
        $this->element->addMember($member->getElement());

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(InterfaceType $element): static
    {
        $interface = new static($element->getName());

        $interface->element = $element;

        return $interface;
    }

    /**
     * @internal
     */
    public function getElement(): InterfaceType
    {
        return $this->element;
    }
}
