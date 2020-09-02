<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\Exception\ReactorException;

/**
 * Provides ability to set access level for element.
 */
trait AccessTrait
{
    /**
     * @var string
     */
    private $access = AbstractDeclaration::ACCESS_PRIVATE;

    /**
     * @param string $access
     *
     * @return $this
     * @throws ReactorException
     */
    public function setAccess(string $access): self
    {
        if (
            !in_array($access, [
            AbstractDeclaration::ACCESS_PRIVATE,
            AbstractDeclaration::ACCESS_PROTECTED,
            AbstractDeclaration::ACCESS_PUBLIC,
            ], true)
        ) {
            throw new ReactorException("Invalid declaration level '{$access}'");
        }

        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @return $this
     */
    public function setPublic(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PUBLIC);

        return $this;
    }

    /**
     * @return $this
     */
    public function setProtected(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PROTECTED);

        return $this;
    }

    /**
     * @return $this
     */
    public function setPrivate(): self
    {
        $this->setAccess(AbstractDeclaration::ACCESS_PRIVATE);

        return $this;
    }
}
