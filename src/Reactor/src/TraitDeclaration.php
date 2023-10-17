<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\TraitType;
use Spiral\Reactor\Partial\Constant;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\Property;
use Spiral\Reactor\Partial\TraitUse;
use Spiral\Reactor\Traits;

/**
 * @extends AbstractDeclaration<TraitType>
 */
class TraitDeclaration extends AbstractDeclaration implements AggregableInterface
{
    use Traits\ConstantsAware;
    use Traits\MethodsAware;
    use Traits\PropertiesAware;
    use Traits\TraitsAware;

    public function __construct(string $name)
    {
        $this->element = new TraitType($name);
    }

    public function addMember(Method|Property|Constant|TraitUse $member): static
    {
        $this->element->addMember($member->getElement());

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(TraitType $element): static
    {
        $trait = new static($element->getName());

        $trait->element = $element;

        return $trait;
    }

    /**
     * @internal
     */
    public function getElement(): TraitType
    {
        return $this->element;
    }
}
