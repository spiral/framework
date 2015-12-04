<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\ClassElements\MethodElement;
use Spiral\Reactor\ClassElements\PropertyElement;

/**
 * Represent class declaration.
 */
class ClassDeclaration extends AbstractElement
{
    /**
     * Parent class (extends).
     *
     * @var string
     */
    private $extends = '';

    /**
     * @var array
     */
    private $interfaces = [];

    /**
     * @var array
     */
    private $constants = [];

    /**
     * @var array
     */
    private $constantComments = [];

    /**
     * @var PropertyElement[]
     */
    private $properties = [];

    /**
     * @var MethodElement[]
     */
    private $methods = [];

    /**
     * @param string $class Class name.
     * @return $this
     */
    public function setExtends($class)
    {
        $this->extends = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Set new list of implemented interfaces.
     *
     * @param array $interfaces
     * @return $this
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = [];

        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }

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
     * @param string              $name
     * @param mixed|PHPExpression $value
     * @param array               $comment Comment lines.
     * @return $this
     */
    public function setConstant($name, $value, array $comment = [])
    {
        $this->constants[$name] = $value;

        if (!empty($comment)) {
            $this->constantComments[$name] = $comment;
        }

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
        unset($this->constantComments[$name]);

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
     * @param string       $name
     * @param string|array $comment Forced property docComment.
     * @return PropertyElement
     */
    public function property($name, $comment = null)
    {
        if (!$this->hasProperty($name)) {
            $this->properties[$name] = new PropertyElement($name);
        }

        if (!empty($comment)) {
            $this->properties[$name]->setComment($comment);
        }

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
     * @param mixed  $comment    Forced method doc comment.
     * @param array  $parameters Forced list of parameters in a form [name => type] or just names.
     * @return MethodElement
     */
    public function method($name, $comment = null, array $parameters = [])
    {
        if (!$this->hasMethod($name)) {
            $this->methods[$name] = new MethodElement($name);
        }

        if (!empty($comment)) {
            $this->methods[$name]->setComment($comment);
        }

        foreach ($parameters as $parameter => $comment) {
            if (is_numeric($parameter)) {
                //Provided as non associated array
                $this->methods[$name]->parameter($parameter = $comment);
            } else {
                $this->methods[$name]->parameter($parameter, $comment);
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
     * @param ArraySerializer|null $serializer
     */
    public function lines($indent = 0, ArraySerializer $serializer = null)
    {
        $lines = $this->commentLines($this->comment);
        $header = "class {$this->getName()}" . ($this->extends ? " extends {$this->extends} " : '');

        if (!empty($this->interfaces)) {
            $header .= ' implements ' . join(', ', $this->interfaces);
        }

        $lines[] = $header;
        $lines[] = "{";

        foreach ($this->constants as $constant => $value) {
            if ($value instanceof PHPExpression) {
                $value = $value->getValue();
            } else {
                $value = var_export($value, true);
            }

            if (!empty($this->constantComments[$constant])) {
                $lines = array_merge(
                    $lines,
                    $this->commentLines($this->constantComments[$constant], 1)
                );
            }

            $lines[] = $this->indent("const {$constant} = {$value};", 1);
            $lines[] = "";
        }

        foreach ($this->properties as $property) {
            $lines = array_merge(
                $lines, $property->lines(1, $serializer)
            );

            $lines[] = "";
        }

        foreach ($this->methods as $method) {
            $lines = array_merge(
                $lines, $method->lines(1)
            );

            $lines[] = "";
        }

        if ($lines[count($lines) - 1] == "") {
            //We don't need blank lines at the end
            unset($lines[count($lines) - 1]);
        }

        $lines[] = '}';

        return $this->indentLines($lines, $indent);
    }

    /**
     * Copy properties, methods and constants from existed class using reflection.
     * Attention, for compatibility reasons parent class and interfaces will be copied using
     * absolute name.
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

        !empty($reflection->getParentClass()) && $this->setExtends(
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
        $this->extends = [];
        $this->interfaces = [];

        parent::flushSchema();
    }
}