<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\Constant as NetteConstant;
use Spiral\Reactor\Aggregator\Constants;
use Spiral\Reactor\Partial\Constant;

/**
 * @internal
 */
trait ConstantsAware
{
    public function setConstants(Constants $constants): static
    {
        $this->element->setConstants(\array_map(
            static fn (Constant $constant) => $constant->getElement(),
            \iterator_to_array($constants)
        ));

        return $this;
    }

    public function getConstants(): Constants
    {
        return new Constants(\array_map(
            static fn (NetteConstant $constant) => Constant::fromElement($constant),
            $this->element->getConstants()
        ));
    }

    public function addConstant(string $name, mixed $value): Constant
    {
        return Constant::fromElement($this->element->addConstant($name, $value));
    }

    public function getConstant(string $name): Constant
    {
        return $this->getConstants()->get($name);
    }

    public function removeConstant(string $name): static
    {
        $this->element->removeConstant($name);

        return $this;
    }
}
