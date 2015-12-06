<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Database\Entities\Schemas\AbstractColumn;
use Spiral\Reactor\ClassDeclaration\Aggregators\ConstantAggregator;
use Spiral\Reactor\ClassDeclaration\Aggregators\MethodAggregator;
use Spiral\Reactor\ClassDeclaration\Aggregators\PropertyAggregator;
use Spiral\Reactor\ClassElements\ConstantDeclaration;
use Spiral\Reactor\ClassElements\MethodDeclaration;
use Spiral\Reactor\ClassElements\PropertyDeclaration;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;

/**
 * Class declaration.
 *
 * @property ConstantAggregator|ConstantDeclaration[] $constants
 * @property PropertyAggregator|PropertyDeclaration[] $properties
 * @property MethodAggregator|MethodDeclaration[]     $methods
 * @property DocComment                               $comment
 */
class ClassDeclaration extends Declaration implements ReplaceableInterface
{
    /**
     * Can be commented.
     */
    use CommentTrait;

    /**
     * @var string
     */
    private $name = '';

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
     * @throws ReactorException When name is invalid.
     */
    public function __construct($name, $extends = '', array $interfaces = [])
    {
        $this->setName($name);
        if (!empty($extends)) {
            $this->setExtends($extends);
        }
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
        $this->traits[ltrim($interface, '\\')] = true;

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
     * @return DeclarationAggregator
     */
    public function constants()
    {
        return $this->constants;
    }

    /**
     * @return DeclarationAggregator
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * @return DeclarationAggregator
     */
    public function methods()
    {
        return $this->methods;
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
     * @todo DRY
     * @param string $name
     * @return mixed
     * @throws ReactorException
     */
    public function __get($name)
    {
        switch ($name) {
            case 'constants':
                return $this->constants();
            case 'properties':
                return $this->properties();
            case 'methods':
                return $this->methods();
            case 'comment':
                return $this->comment();
        }

        throw new ReactorException("Undefined property '{$name}'.");
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function render($indentLevel = 0)
    {
        $result = '';

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel);
        }

        //Class header
        $header = "class {$this->name}";

        //Rendering extends
        if (!empty($this->extends)) {
            $header .= " extends {$this->extends}";
        }

        if (!empty($this->interfaces)) {
            $interfaces = join(", ", array_keys($this->interfaces));
            $header .= "implements {$interfaces}";
        }

        $result .= $this->indent($header, $indentLevel) . "\n";
        $result .= $this->indent("{", $indentLevel) . "\n";

        //Rendering content
        if (!empty($this->traits)) {
            $result .= $this->renderTraits($indentLevel + 1) . "\n";
        }

        if (!$this->constants->isEmpty()) {
            $result .= "\n" . $this->constants->render($indentLevel + 1) . "\n";
        }

        if (!$this->properties->isEmpty()) {
            $result .= "\n" . $this->properties->render($indentLevel + 1) . "\n";
        }

        if (!$this->methods->isEmpty()) {
            $result .= "\n" . $this->methods->render($indentLevel + 1) . "\n";
        }

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
            $lines = $this->indent("use {$class};", $indentLevel);
        }

        return join("\n", $lines);
    }
}