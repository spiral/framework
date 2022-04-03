<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\Exception\ReactorException;

/**
 * Provides ability to set access level for element.
 */
trait AccessTrait
{
    private string $access = AbstractDeclaration::ACCESS_PRIVATE;

    public function setAccess(string $access): self
    {
        if (
            !\in_array($access, [
            AbstractDeclaration::ACCESS_PRIVATE,
            AbstractDeclaration::ACCESS_PROTECTED,
            AbstractDeclaration::ACCESS_PUBLIC,
            ], true)
        ) {
            throw new ReactorException(\sprintf("Invalid declaration level '%s'", $access));
        }

        $this->access = $access;

        return $this;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function setPublic(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PUBLIC);

        return $this;
    }

    public function setProtected(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PROTECTED);

        return $this;
    }

    public function setPrivate(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PRIVATE);

        return $this;
    }
}
