<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\ClassElements;

use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\ArraySerializer;

/**
 * Class property element.
 */
class PropertyElement extends AbstractElement
{
    /**
     * @var string
     */
    private $access = self::ACCESS_PUBLIC;

    /**
     * @var bool
     */
    private $static = false;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * @param string $access
     * @return $this
     */
    public function setAccess($access)
    {
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
     * @param bool $static
     * @return $this
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * Mark property as default and specify default value.
     *
     * @param bool  $default
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault($default, $defaultValue = null)
    {
        $this->defaultValue = null;
        if ($this->default = (bool)$default) {
            $this->defaultValue = $defaultValue;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param ArraySerializer $serializer Class used to render array values for default properties and etc.
     * @param int             $position   Internal value.
     */
    public function render($indentLevel = 0, ArraySerializer $serializer = null, $position = 0)
    {
        $result = [
            !$position ? ltrim($this->renderComment($indentLevel)) : $this->renderComment($indentLevel)
        ];

        $property = $this->access . ' ' . ($this->static ? 'static ' : '') . '$' . $this->getName();

        if (!$this->isDefault()) {
            $result[] = $property . ';';

            return $this->join($result, $indentLevel);
        }

        if (is_array($this->defaultValue)) {
            $serializer = !empty($serializer) ? $serializer : new ArraySerializer();
            $value = explode("\n", $serializer->serialize($this->defaultValue, self::INDENT));

            foreach ($value as &$line) {
                $line = $this->indent($line, $indentLevel);
                unset($line);
            }

            $value[0] = ltrim($value[0]);
            $property .= ' = ' . join("\n", $value);
        } else {
            $property .= ' = ' . var_export($this->defaultValue, true);
        }

        $result[] = $property . ';';

        return $this->join($result, $indentLevel);
    }

    /**
     * Clone parameter options using ReflectionProperty.
     *
     * @param \ReflectionProperty $property
     */
    public function cloneSchema(\ReflectionProperty $property)
    {
        $this->setDefault(false);
        $this->setComment($property->getDocComment());
        $this->static = $property->isStatic();

        if ($property->isPrivate()) {
            $this->setAccess(self::ACCESS_PRIVATE);
        } elseif ($property->isProtected()) {
            $this->setAccess(self::ACCESS_PROTECTED);
        }

        if (!$property->isDefault()) {
            return;
        }

        $parentDefaults = $property->getDeclaringClass()->getDefaultProperties();
        foreach ($parentDefaults as $name => $defaultValue) {
            if ($name == $property->getName()) {
                $this->setDefault(true, $defaultValue);
                break;
            }
        }
    }
}