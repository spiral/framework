<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Prototypes;

use Spiral\Reactor\Exceptions\ReactorException;

/**
 * Declaration with name.
 */
abstract class NamedDeclaration extends Declaration
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * @param string $name
     * @return $this
     * @throws ReactorException
     */
    public function setName($name)
    {
        if (!preg_match('/^[a-z_0-9]+$/i', $name)) {
            throw new ReactorException("Invalid element  name '{$name}'.");
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}