<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor;

use Spiral\Reactor\ClassElements\MethodElement;
use Spiral\Reactor\ClassElements\PHPConstant;
use Spiral\Reactor\ClassElements\PropertyElement;

/**
 * Represent class declaration.
 */
class ClassElement extends AbstractElement
{
    /**
     * Parent class (extends).
     *
     * @var string
     */
    private $parent = '';

    /**
     * @var array
     */
    private $interfaces = [];

    /**
     * @var array
     */
    private $constants = [];

    /**
     * @var PropertyElement[]
     */
    private $properties = [];

    /**
     * @var MethodElement[]
     */
    private $methods = [];

    /**
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $class Class name.
     * @return $this
     */
    public function setParent($class)
    {
        $this->parent = $class;

        return $this;
    }

    /**
     * Set new list of implemented interfaces.
     *
     * @param array $interfaces
     * @return $this
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = $interfaces;

        return $this;
    }

    /**
     * @param string $interface
     * @return $this
     */
    public function addInterface($interface)
    {
        $interface = ltrim($interface, '\\');
        if (array_search($interface, $this->interfaces) === false) {
            $this->interfaces[] = $interface;
        }

        return $this;
    }

    /**
     * @param string $interface
     * @return $this
     */
    public function removeInterface($interface)
    {
        $interface = ltrim($interface, '\\');
        if (($index = array_search($interface, $this->interfaces)) !== false) {
            unset($this->interfaces[$index]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * @param string            $name
     * @param mixed|PHPConstant $value
     * @return $this
     */
    public function setConstant($name, $value)
    {
        $this->constants[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasConstant($name)
    {
        return array_key_exists($name, $this->constants);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeConstant($name)
    {
        unset($this->constants[$name]);

        return $this;
    }

    /**
     * @return array
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * Get existed or create new property under given name.
     *
     * @param string $name
     * @param mixed  $docComment Forced property docComment.
     * @return PropertyElement
     */
    public function property($name, $docComment = null)
    {
        if (!$this->hasProperty($name)) {
            $this->properties[$name] = new PropertyElement($name);
        }

        !empty($docComment) && $this->properties[$name]->setComment($docComment);

        return $this->properties[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);

        return $this;
    }

    /**
     * @return PropertyElement[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get existed or create new class method.
     *
     * @param string $name       Method name.
     * @param mixed  $docComment Forced method doc comment.
     * @param array  $parameters Forced list of parameters in a form [name => type] or just names.
     * @return MethodElement
     */
    public function method($name, $docComment = null, array $parameters = [])
    {
        if (!$this->hasMethod($name)) {
            $this->methods[$name] = new MethodElement($name);
        }

        !empty($docComment) && $this->methods[$name]->setComment($docComment);

        foreach ($parameters as $parameter => $docComment) {
            if (is_numeric($parameter)) {
                //Provided as non associated array
                $this->methods[$name]->parameter($parameter = $docComment);
            } else {
                $this->methods[$name]->parameter($parameter, $docComment);
            }
        }

        return $this->methods[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMethod($name)
    {
        return array_key_exists($name, $this->methods);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeMethod($name)
    {
        unset($this->methods[$name]);

        return $this;
    }

    /**
     * @return MethodElement[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceComments($search, $replace)
    {
        parent::replaceComments($search, $replace);

        foreach ($this->methods as $method) {
            $method->replaceComments($search, $replace);
        }

        foreach ($this->properties as $property) {
            $property->replaceComments($search, $replace);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param ArraySerializer $serializer Class used to render array values for default properties and etc.
     */
    public function render($indentLevel = 0, ArraySerializer $serializer = null)
    {
        $result = [$this->renderComment($indentLevel)];
        $header = 'class ' . $this->getName() . ($this->parent ? ' extends ' . $this->parent : '');

        if (!empty($this->interfaces)) {
            $header .= ' implements ' . join(', ', $this->interfaces);
        }

        $result[] = $header;
        $result[] = "{";

        foreach ($this->constants as $constant => $value) {

            if ($value instanceof PHPConstant) {
                $value = $value->getValue();
            } else {
                $value = var_export($value, true);
            }

            $result[] = $this->indent(
                'const ' . $constant . ' = ' . $value . ';',
                $indentLevel + 1
            );
        }

        if (!empty($this->constants)) {
            //Blank line
            $result[] = '';
        }

        $position = 0;
        foreach ($this->properties as $property) {
            $result[] = $property->render($indentLevel + 1, $serializer, $position++);
        }

        foreach ($this->methods as $method) {
            $result[] = $method->render($indentLevel + 1, $position++);
        }

        $result[] = '}';

        return $this->join($result, $indentLevel);
    }

    /**
     * Copy properties, methods and constants from existed class using reflection.
     *
     * @param string $class
     * @param bool   $ownData     Copy only data declared in specified class, not in it's parents.
     * @param bool   $flushSchema Flush existed schema before copy.
     * @return $this
     */
    public function cloneSchema($class, $ownData = false, $flushSchema = true)
    {
        $reflection = new \ReflectionClass($class);

        $flushSchema && $this->flushSchema();

        !empty($reflection->getParentClass()) && $this->setParent(
            $reflection->getParentClass()->getName()
        );

        $this->setComment($reflection->getDocComment());

        $this->interfaces = array_unique(
            array_merge($this->interfaces, $reflection->getInterfaceNames())
        );

        $this->constants = array_unique(
            array_merge($this->constants, $reflection->getConstants())
        );

        foreach ($reflection->getProperties() as $property) {
            if ($ownData && !empty($reflection->getParentClass())) {
                if ($reflection->getParentClass()->hasProperty($property->getName())) {
                    continue;
                }
            }

            $this->property($property->getName())->cloneSchema($property);
        }

        foreach ($reflection->getMethods() as $method) {
            if ($ownData && !empty($reflection->getParentClass())) {
                if ($reflection->getParentClass()->hasMethod($method->getName())) {
                    continue;
                }
            }

            $this->method($method->getName())->cloneSchema($method);
        }

        return $this;
    }

    /**
     * Remove all properties, methods and constants.
     */
    public function flushSchema()
    {
        $this->properties = [];
        $this->constants = [];
        $this->methods = [];
        $this->parent = [];
        $this->interfaces = [];

        parent::flushSchema();
    }
}