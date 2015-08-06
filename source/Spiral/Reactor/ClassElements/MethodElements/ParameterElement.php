<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\ClassElements\MethodElements;

/**
 * Single method parameter.
 */
class ParameterElement
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var bool
     */
    private $optional = false;

    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * Passed by reference flag.
     *
     * @var bool
     */
    private $pdb = false;

    /**
     * New Method Parameter.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
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

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Flag that parameter should pass by reference.
     *
     * @param bool $passedByReference
     * @return $this
     */
    public function setPBR($passedByReference = false)
    {
        $this->pdb = $passedByReference;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPBR()
    {
        return $this->pdb;
    }

    /**
     * Mark as optional/required and set default value.
     *
     * @param bool  $optional
     * @param mixed $defaultValue
     * @return $this
     */
    public function setOptional($optional, $defaultValue = null)
    {
        $this->defaultValue = null;
        if ($this->optional = $optional) {
            $this->defaultValue = $defaultValue;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Render property declaration.
     *
     * @return string
     */
    public function render()
    {
        $type = '';
        if ($this->type) {
            $type = $this->type ? ($this->type == 'array' ? '' : '\\') . $this->type . " " : "";
        }

        $result = $type . ($this->pdb ? '&' : '') . "$" . $this->name;

        if (!$this->optional) {
            return $result;
        }

        if ($this->defaultValue === []) {
            $result .= ' = []';
        } else {
            $result .= ' = ' . var_export($this->defaultValue, true);
        }

        return $result;
    }
}