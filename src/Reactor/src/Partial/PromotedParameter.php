<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Nette\PhpGenerator\PromotedParameter as NettePromotedParameter;
use Spiral\Reactor\Traits;

/**
 * @property NettePromotedParameter $element
 */
final class PromotedParameter extends Parameter
{
    use Traits\CommentAware;
    use Traits\VisibilityAware;

    public function __construct(string $name)
    {
        $this->element = new NettePromotedParameter((new InflectorFactory())->build()->camelize($name));
    }

    public function setReadOnly(bool $state = true): self
    {
        $this->element->setReadOnly($state);

        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->element->isReadOnly();
    }

    /**
     * @internal
     */
    public function getElement(): NettePromotedParameter
    {
        return $this->element;
    }
}
