<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\Partial\Visibility;

/**
 * @internal
 */
trait VisibilityAware
{
    public function setVisibility(Visibility $visibility): static
    {
        $this->element->setVisibility($visibility->value);

        return $this;
    }

    public function getVisibility(): ?Visibility
    {
        return Visibility::tryFrom((string)$this->element->getVisibility());
    }

    public function setPublic(): static
    {
        $this->setVisibility(Visibility::PUBLIC);

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->getVisibility() === Visibility::PUBLIC;
    }

    public function setProtected(): static
    {
        $this->setVisibility(Visibility::PROTECTED);

        return $this;
    }

    public function isProtected(): bool
    {
        return $this->getVisibility() === Visibility::PROTECTED;
    }

    public function setPrivate(): static
    {
        $this->setVisibility(Visibility::PRIVATE);

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->getVisibility() === Visibility::PRIVATE;
    }
}
