<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor;

class NamespaceElement extends BaseElement
{
    /**
     * List of classes which are declared in this namespace.
     *
     * @var ClassElement[]
     */
    protected $classes = array();

    /**
     * Namespace uses.
     *
     * @var array
     */
    protected $uses = array();

    /**
     * Add a new class declaration to namespace.
     *
     * @param ClassElement $class
     * @return static
     */
    public function addClass(ClassElement $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    /**
     * Get all classes being used.
     *
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Add a new class usage to namespace.
     *
     * @param string $class Class name.
     * @return static
     */
    public function addUse($class)
    {
        if (array_search($class, $this->uses) === false)
        {
            $this->uses[] = $class;
        }

        return $this;
    }

    /**
     * Replace all used classes with a new given list.
     *
     * @param array $uses
     * @return static
     */
    public function setUses(array $uses)
    {
        $this->uses = $uses;

        return $this;
    }

    /**
     * Render element declaration. This method should be declared in RElement child classes and perform an operation for
     * rendering a specific type of content. Renders namespace section with it's classes, uses and comments.
     *
     * @param int $indentLevel Tabulation level.
     * @return string
     */
    public function createDeclaration($indentLevel = 0)
    {
        $result = array($this->renderComment($indentLevel));

        $result[] = 'namespace ' . trim($this->name, '\\');
        $result[] = "{";

        //Uses
        foreach ($this->uses as $class)
        {
            $result[] = self::applyIndent('use ' . $class . ';', $indentLevel + 1);
        }

        //Classes
        foreach ($this->classes as $class)
        {
            $result[] = $class->createDeclaration($indentLevel + 1);
        }

        $result[] = '}';

        return self::join($result, $indentLevel);
    }
}