<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor\ClassElements;

use Spiral\Support\Generators\Reactor\BaseElement;

class PropertyElement extends BaseElement
{
    /**
     * Property access level (private, protected and public).
     *
     * @var string
     */
    protected $access = self::ACCESS_PUBLIC;

    /**
     * Indicates that property can be accessed statistically.
     *
     * @var bool
     */
    protected $static = false;

    /**
     * Default value is presented.
     *
     * @var bool
     */
    protected $default = false;

    /**
     * Default value.
     *
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * Property access level (private, protected and public).
     *
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set access level.
     *
     * @param string $access Public by default.
     * @return static
     */
    public function setAccess($access = self::ACCESS_PUBLIC)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Indicates that property can be accessed statically.
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * Mark property as static/non static.
     *
     * @param bool $static True if property is static.
     * @return static
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;

        return $this;
    }

    /**
     * True if default value is present.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Get default property value.
     *
     * @return bool
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set default value to property.
     *
     * @param bool  $default      Default value flag (if false, property will be designated as non
     *                            default).
     * @param mixed $defaultValue Property default value (string, array, etc).
     * @return static
     */
    public function setDefault($default, $defaultValue = false)
    {
        $this->defaultValue = null;
        if ($this->default = (bool)$default)
        {
            $this->defaultValue = $defaultValue;
        }

        return $this;
    }

    /**
     * Copy property declaration using ReflectionProperty.
     *
     * @param \ReflectionProperty $property
     */
    public function cloneSchema(\ReflectionProperty $property)
    {
        $this->setComment($property->getDocComment());
        $this->static = $property->isStatic();

        if ($property->isPrivate())
        {
            $this->setAccess(self::ACCESS_PRIVATE);
        }
        elseif ($property->isProtected())
        {
            $this->setAccess(self::ACCESS_PROTECTED);
        }

        if ($property->isDefault())
        {
            foreach ($property->getDeclaringClass()->getDefaultProperties() as $propertyName =>
                     $propertyValue)
            {
                if ($propertyName == $property->getName())
                {
                    $this->setDefault(true, $propertyValue);
                }
            }
        }
    }

    /**
     * Render element declaration. That method must be declared in RElement childs classes and then
     * perform the operation for rendering a specific type of content. Property will be rendered with
     * it's own access level, static flag and default value (if shown).
     *
     * @param int $indentLevel Tabulation level.
     * @param int $position    Element position.
     * @return string
     */
    public function createDeclaration($indentLevel = 0, $position = 0)
    {
        $result = array(
            !$position ? ltrim($this->renderComment($indentLevel)) : $this->renderComment($indentLevel)
        );

        $property = $this->access . ' ' . ($this->static ? 'static ' : '') . '$' . $this->name;

        if ($this->isDefault())
        {
            if ($this->defaultValue === array())
            {
                $property .= ' = array()';
            }
            else
            {
                $property .= ' = ' . var_export($this->defaultValue, true);
            }
        }

        $result[] = $property . ';';

        return self::join($result, $indentLevel);
    }
}