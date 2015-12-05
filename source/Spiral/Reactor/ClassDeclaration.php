<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;

/**
 * Class declaration.
 */
abstract class ClassDeclaration extends Declaration
{
    /**
     * Can be commented.
     */
    use CommentTrait;

    /**
     * @var string
     */
    private $name = '';

    private $traits = [];

    private $constants = null;

    private $properties = null;

    private $methods = null;

    /**
     * @param string $name
     * @throws ReactorException When name is invalid.
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * @param string $name
     * @return $this
     * @throws ReactorException When name is invalid.
     */
    public function setName($name)
    {
        if (!preg_match('/^[a-z_0-9]+$/', $name)) {
            throw new ReactorException("Invalid class name '{$name}'.");
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