<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Nette\PhpGenerator\TraitUse as NetteTraitUse;
use Spiral\Reactor\AggregableInterface;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits;

final class TraitUse implements NamedInterface, AggregableInterface
{
    use Traits\CommentAware;
    use Traits\NameAware;

    private NetteTraitUse $element;

    public function __construct(string $name)
    {
        $this->element = new NetteTraitUse($name);
    }

    public function getResolutions(): array
    {
        return $this->element->getResolutions();
    }

    public function addResolution(string $resolution): self
    {
        $this->element->addResolution($resolution);

        return $this;
    }

    /**
     * @internal
     */
    public static function fromElement(NetteTraitUse $element): self
    {
        $traitUse = new self($element->getName());

        $traitUse->element = $element;

        return $traitUse;
    }

    /**
     * @internal
     */
    public function getElement(): NetteTraitUse
    {
        return $this->element;
    }
}
