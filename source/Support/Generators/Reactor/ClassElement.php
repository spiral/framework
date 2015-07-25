<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor;

use Spiral\Support\Generators\Reactor\ClassElements\MethodElement;
use Spiral\Support\Generators\Reactor\ClassElements\PropertyElement;

class ClassElement extends BaseElement
{
    /**
     * Name of the parent class which is being extended.
     *
     * @var string
     */
    protected $parent = '';

    /**
     * Name(s) of the interfaces being implemented by this class.
     *
     * @var array
     */
    protected $interfaces = [];

    /**
     * Properties and their default values declared in this class.
     *
     * @var PropertyElement[]
     */
    protected $properties = [];

    /**
     * Constants and their values declared in this class.
     *
     * @var array
     */
    protected $constants = [];

    /**
     * Public static and non-static methods declared in this class. Method can include source code
     * which allows you to use reactor output as real classes.
     *
     * @var MethodElement[]
     */
    protected $methods = [];

    /**
     * Name of the parent class which is being extended.
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent class name.
     *
     * @param string $class Class name.
     * @return $this
     */
    public function setParent($class)
    {
        $this->parent = $class;

        return $this;
    }

    /**
     * Name(s) of interfaces being implemented by this class.
     *
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * Add a new implemented interface to the class declaration.
     *
     * @param string $interface Interface name.
     * @return $this
     */
    public function addInterface($interface)
    {
        if (array_search($interface, $this->interfaces) === false)
        {
            $this->interfaces[] = $interface;
        }

        return $this;
    }

    /**
     * Remove a implemented interface by it's name.
     *
     * @param string $interface Interface name.
     * @return $this
     */
    public function removeInterface($interface)
    {
        if (($index = array_search($interface, $this->interfaces)) !== false)
        {
            unset($this->interfaces[$index]);
        }

        return $this;
    }

    /**
     * Replace the implemented interfaces with a new given list.
     *
     * @param array $interfaces Array of interface names.
     * @return $this
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = $interfaces;

        return $this;
    }

    /**
     * Get list of all class properties and their implementations.
     *
     * @return PropertyElement[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Check if class declaration has a specified property by it's name.
     *
     * @param string $name Property name.
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Remove property from declaration by it's name.
     *
     * @param string $name Property name.
     * @return $this
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);

        return $this;
    }

    /**
     * Get/create property. Property will automatically be created during the first call of this method.
     *
     * @param string $name       Property name.
     * @param mixed  $docComment Property doc comment (to set or replace existing).
     * @return PropertyElement
     */
    public function property($name, $docComment = null)
    {
        if (!$this->hasProperty($name))
        {
            $this->properties[$name] = new PropertyElement($name);
        }

        $docComment && $this->properties[$name]->setComment($docComment);

        return $this->properties[$name];
    }

    /**
     * List of all class constants and their values.
     *
     * @return array
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * Check if constant exists in the class declaration by it's name.
     *
     * @param string $name Constant name.
     * @return bool
     */
    public function hasConstant($name)
    {
        return array_key_exists($name, $this->constants);
    }

    /**
     * Remove constant by name.
     *
     * @param string $name Constant name.
     * @return $this
     */
    public function removeConstant($name)
    {
        unset($this->constants[$name]);

        return $this;
    }

    /**
     * Set class constant value under a given name.
     *
     * @param string $name  Constant name.
     * @param mixed  $value Constant value.
     * @return $this
     */
    public function setConstant($name, $value)
    {
        $this->constants[$name] = $value;

        return $this;
    }

    /**
     * List of all class methods and their declarations.
     *
     * @return MethodElement[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Check if the method exists in class declaration by name.
     *
     * @param string $name Method name.
     * @return bool
     */
    public function hasMethod($name)
    {
        return array_key_exists($name, $this->methods);
    }

    /**
     * Remove method from class declaration by name.
     *
     * @param string $name Method name.
     * @return $this
     */
    public function removeMethod($name)
    {
        unset($this->methods[$name]);

        return $this;
    }

    /**
     * Get/create method. Method will automatically be created during the first call of this method.
     *
     * @param string $name       Method name.
     * @param mixed  $docType    DocComment to set or replace.
     * @param array  $parameters List of parameters or parameters associated with their docComment.
     * @return MethodElement
     */
    public function method($name, $docType = null, array $parameters = [])
    {
        if (!$this->hasMethod($name))
        {
            $this->methods[$name] = new MethodElement($name);
        }

        if ($docType)
        {
            $this->methods[$name]->setComment($docType);
        }

        if ($parameters)
        {
            foreach ($parameters as $parameter => $docType)
            {
                if (is_numeric($parameter))
                {
                    $this->methods[$name]->parameter($parameter = $docType);
                }
                else
                {
                    $this->methods[$name]->parameter($parameter, $docType);
                }
            }
        }

        return $this->methods[$name];
    }

    /**
     * Replace strings in all doc comment lines or other names. This is helpful when you want to build
     * a virtual documentation class based on another declaration. The string will be replaced in
     * everywhere it occurs (methods, properties, constants and class docComments).
     *
     * @param string|array $search  String to find.
     * @param string|array $replace String to replace.
     * @return $this
     */
    public function replaceComments($search, $replace)
    {
        parent::replaceComments($search, $replace);

        foreach ($this->methods as $method)
        {
            $method->replaceComments($search, $replace);
        }

        foreach ($this->properties as $property)
        {
            $property->replaceComments($search, $replace);
        }

        return $this;
    }

    /**
     * Render element declaration. This method should be declared in the RElement childs classes and
     * perform operation for rendering specific type of content. This will render class declaration
     * with it's methods, properties, constants and comments.
     *
     * @param int $indentLevel Tabulation level.
     * @return string
     */
    public function createDeclaration($indentLevel = 0)
    {
        $result = [$this->renderComment($indentLevel)];

        $header = 'class ' . $this->name . ($this->parent ? ' extends ' . $this->parent : '');

        if ($this->interfaces)
        {
            $header .= ' implements \\' . join(', \\', $this->interfaces);
        }

        $result[] = $header;
        $result[] = "{";

        $position = 0;

        //Constants
        foreach ($this->constants as $constant => $value)
        {
            $result[] = static::setIndent(
                'const ' . $constant . ' = ' . var_export($value, true) . ';',
                $indentLevel + 1
            );
        }

        //Properties
        foreach ($this->properties as $property)
        {
            $result[] = $property->createDeclaration($indentLevel + 1, $position++);
        }

        //Methods
        foreach ($this->methods as $method)
        {
            $result[] = $method->createDeclaration($indentLevel + 1, $position++);
        }

        $result[] = '}';

        return static::join($result, $indentLevel);
    }

    /**
     * Clone declaration including parent constants, interfaces and properties from an external class.
     * Specified class will be set as a declaration parent (extends). Use the parentMethods argument
     * to additionally clone every parent method.
     *
     * @param string $class         Class to be cloned.
     * @param bool   $parentMethods Set to true to clone methods declared in parent class.
     * @param bool   $flushSchema   Clear current class schema before cloning.
     * @return $this
     */
    public function cloneSchema($class, $parentMethods = false, $flushSchema = true)
    {
        $flushSchema && $this->flushSchema();

        $reflection = new \ReflectionClass($class);

        //Extends
        if ($parent = $reflection->getParentClass())
        {
            $this->setParent($reflection->getParentClass()->getName());
        }

        //DocComment
        $this->setComment($reflection->getDocComment());

        //Implements
        $this->interfaces = $reflection->getInterfaceNames();

        //Constants
        $this->constants = $reflection->getConstants();

        foreach ($reflection->getProperties() as $property)
        {
            if (!$parentMethods && $reflection->getParentClass())
            {
                if ($reflection->getParentClass()->hasProperty($property->getName()))
                {
                    continue;
                }
            }

            $this->property($property->getName())->cloneSchema($property);
        }

        //Methods
        $this->methods = [];
        foreach ($reflection->getMethods() as $method)
        {
            if (!$parentMethods && $reflection->getParentClass())
            {
                if ($reflection->getParentClass()->hasMethod($method->getName()))
                {
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
        $this->docComment = [];
        $this->parent = [];
        $this->interfaces = [];
    }
}