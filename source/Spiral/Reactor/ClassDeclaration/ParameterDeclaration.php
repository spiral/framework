<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\ClassDeclaration;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\SerializerTrait;

/**
 * Single method parameter.
 */
class ParameterDeclaration extends NamedDeclaration
{
    /**
     *
     */
    use SerializerTrait;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var bool
     */
    private $isOptional = false;

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
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return parent::setName(Inflector::camelize($name));
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
     * Check if parameter is optional.
     *
     * @return bool
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

    /**
     * Set parameter default value.
     *
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault($defaultValue)
    {
        $this->isOptional = null;
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->defaultValue;
    }

    /**
     * Remove default value.
     *
     * @return $this
     */
    public function removeDefault()
    {
        $this->isOptional = false;
        $this->defaultValue = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $type = '';
        if (!empty($this->type)) {
            $type = $this->type . " ";
        }

        $result = $type . ($this->pdb ? '&' : '') . "$" . $this->getName();

        if (!$this->isOptional) {
            return $result;
        }

        return $result . ' = ' . $this->serializer()->serialize($this->defaultValue, $indentLevel);
    }
}