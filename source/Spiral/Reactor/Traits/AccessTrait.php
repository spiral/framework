<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Reactor\DeclarationAggregator;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\Declaration;

/**
 * Provides ability to set access level for element.
 */
trait AccessTrait
{
    /**
     * @var string
     */
    private $access = Declaration::ACCESS_PRIVATE;

    /**
     * @param string $access
     * @return $this
     * @throws ReactorException
     */
    public function setAccess($access)
    {
        if (!in_array($access, [
            Declaration::ACCESS_PRIVATE,
            Declaration::ACCESS_PROTECTED,
            Declaration::ACCESS_PUBLIC
        ])
        ) {
            throw new ReactorException("Invalid declaration level '{$access}'.");
        }

        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @return $this
     */
    public function setPublic()
    {
        $this->setAccess(DeclarationAggregator::ACCESS_PUBLIC);

        return $this;
    }

    /**
     * @return $this
     */
    public function setProtected()
    {
        $this->setAccess(DeclarationAggregator::ACCESS_PROTECTED);

        return $this;
    }

    /**
     * @return $this
     */
    public function setPrivate()
    {
        $this->setAccess(DeclarationAggregator::ACCESS_PRIVATE);

        return $this;
    }
}