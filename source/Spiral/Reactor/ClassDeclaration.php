<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\ClassDeclaration\Aggregators\ConstantAggregator;
use Spiral\Reactor\ClassDeclaration\Aggregators\MethodAggregator;
use Spiral\Reactor\ClassDeclaration\Aggregators\PropertyAggregator;
use Spiral\Reactor\ClassDeclaration\ConstantDeclaration;
use Spiral\Reactor\ClassDeclaration\MethodDeclaration;
use Spiral\Reactor\ClassDeclaration\PropertyDeclaration;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\CommentTrait;

/**
 * Class declaration.
 *
 * @todo interface, trait declarations
 */
class ClassDeclaration extends NamedDeclaration implements ReplaceableInterface
{
    /**
     * Can be commented.
     */
    use CommentTrait;

    /**
     * @var string
     */
    private $extends = '';

    /**
     * @var array
     */
    private $interfaces = [];

    /**
     * Class traits.
     *
     * @var array
     */
    private $traits = [];

    /**
     * @var ConstantAggregator
     */
    private $constants = null;

    /**
     * @var PropertyAggregator
     */
    private $properties = null;

    /**
     * @var MethodAggregator
     */
    private $methods = null;

    /**
     * @param string $name
     * @param string $extends
     * @param array  $interfaces
     * @param string $comment
     * @throws ReactorException When name is invalid.
     */
    public function __construct($name, $extends = '', array $interfaces = [], $comment = '')
    {
        parent::__construct($name);

        if (!empty($extends)) {
            $this->setExtends($extends);
        }

        $this->setInterfaces($interfaces);
        $this->initComment($comment);

        $this->constants = new ConstantAggregator([]);
        $this->properties = new PropertyAggregator([]);
        $this->methods = new MethodAggregator([]);
    }

    /**
     * @param string $class Class name.
     * @return $this
     */
    public function setExtends($class)
    {
        $this->extends = ltrim($class, '\\');

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
     * @param string $interface
     * @return bool
     */
    public function hasInterface($interface)
    {
        $interface = ltrim($interface, '\\');

        return isset($this->interfaces[$interface]);
    }

    /**
     * Declare class interfaces.
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
        $this->interfaces[ltrim($interface, '\\')] = true;

        return $this;
    }

    /**
     * @param string $interface
     * @return $this
     */
    public function removeInterface($interface)
    {
        unset($this->interfaces[ltrim($interface, '\\')]);

        return $this;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function hasTrait($class)
    {
        $class = ltrim($class, '\\');

        return isset($this->traits[$class]);
    }

    /**
     * Declare class traits.
     *
     * @param array $traits
     * @return $this
     */
    public function setTraits(array $traits)
    {
        $this->traits = [];
        foreach ($traits as $trait) {
            $this->addTrait($trait);
        }

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addTrait($class)
    {
        $this->traits[ltrim($class, '\\')] = true;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function removeTrait($class)
    {
        unset($this->traits[ltrim($class, '\\')]);

        return $this;
    }

    /**
     * @return array
     */
    public function getUses()
    {
        return array_keys($this->traits);
    }

    /**
     * @return ConstantAggregator|ConstantDeclaration[]
     */
    public function constants()
    {
        return $this->constants;
    }

    /**
     * @param string $name
     * @return ConstantDeclaration
     */
    public function constant($name)
    {
        return $this->constants->get($name);
    }

    /**
     * @return PropertyAggregator|PropertyDeclaration[]
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @return PropertyDeclaration
     */
    public function property($name)
    {
        return $this->properties->get($name);
    }

    /**
     * @return MethodAggregator|MethodDeclaration[]
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * @param string $name
     * @return MethodDeclaration
     */
    public function method($name)
    {
        return $this->methods->get($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function replace($search, $replace)
    {
        $this->constants->replace($search, $replace);
        $this->properties->replace($search, $replace);
        $this->methods->replace($search, $replace);

        return $this;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function render($indentLevel = 0)
    {
        $result = '';

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        //Class header
        $header = "class {$this->getName()}";

        //Rendering extends
        if (!empty($this->extends)) {
            $header .= " extends {$this->extends}";
        }

        if (!empty($this->interfaces)) {
            $interfaces = join(", ", array_keys($this->interfaces));
            $header .= " implements {$interfaces}";
        }

        $result .= $this->indent($header, $indentLevel) . "\n";
        $result .= $this->indent("{", $indentLevel) . "\n";

        //Rendering content
        if (!empty($this->traits)) {
            $result .= $this->renderTraits($indentLevel + 1) . "\n\n";
        }

        if (!$this->constants->isEmpty()) {
            $result .= $this->constants->render($indentLevel + 1) . "\n\n";
        }

        if (!$this->properties->isEmpty()) {
            $result .= $this->properties->render($indentLevel + 1) . "\n\n";
        }

        if (!$this->methods->isEmpty()) {
            $result .= $this->methods->render($indentLevel + 1) . "\n\n";
        }

        $result = rtrim($result, "\n") . "\n";
        $result .= $this->indent("}", $indentLevel);

        return $result;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    private function renderTraits($indentLevel = 0)
    {
        $lines = [];
        foreach ($this->traits as $class => $options) {
            $lines[] = $this->indent("use {$class};", $indentLevel);
        }

        return join("\n", $lines);
    }
}