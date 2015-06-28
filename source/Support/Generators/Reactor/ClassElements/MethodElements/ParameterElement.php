<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor\ClassElements\MethodElements;

use Spiral\Core\Component;

class ParameterElement extends Component
{
    /**
     * Parameter name. This is not inherited from element.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Parameter is optional.
     *
     * @var bool
     */
    protected $options = false;

    /**
     * Default value for a parameter (only if this is optional).
     *
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * Parameter type (only use class names or arrays).
     *
     * @var string
     */
    protected $type = '';

    /**
     * Passed by reference flag.
     *
     * @var bool
     */
    protected $pdb = false;

    /**
     * New parameter object.
     *
     * @param string $name Parameter name.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Parameter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Overwrite the parameter's name.
     *
     * @param string $name New parameter name.
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Parameter type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Change parameter type.
     *
     * @param string $type Class name or "array".
     * @return static
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Is the parameter passed by reference.
     *
     * @return bool
     */
    public function isPBR()
    {
        return $this->pdb;
    }

    /**
     * Flag that parameter should pass by reference.
     *
     * @param bool $passedByReference
     * @return static
     */
    public function setPBR($passedByReference = false)
    {
        $this->pdb = $passedByReference;

        return $this;
    }

    /**
     * Is parameter optional.
     *
     * @return bool
     */
    public function isOptional()
    {
        return $this->options;
    }

    /**
     * Default value (only if this is optional).
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Mark as optional/required and set up default value.
     *
     * @param bool  $optional     True if parameter option.
     * @param mixed $defaultValue Parameter default value.
     * @return static
     */
    public function setOptional($optional, $defaultValue = null)
    {
        $this->defaultValue = null;
        if ($this->options = (bool)$optional)
        {
            $this->defaultValue = $defaultValue;
        }

        return $this;
    }

    /**
     * Render element declaration. This method must be declared in RElement child classes and then
     * perform operation for rendering specific type of content. Method parameter is embedded to
     * method declaration.
     *
     * @return string
     */
    public function createDeclaration()
    {
        $type = '';
        if ($this->type)
        {
            $type = $this->type ? ($this->type == 'array' ? '' : '\\') . $this->type . " " : "";
        }

        $result = $type . ($this->pdb ? '&' : '') . "$" . $this->name;

        if ($this->options)
        {
            if ($this->defaultValue === [])
            {
                $result .= ' = array()';
            }
            else
            {
                $result .= ' = ' . var_export($this->defaultValue, true);
            }
        }

        return $result;
    }
}