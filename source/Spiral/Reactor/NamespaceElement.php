<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor;

/**
 * Represent namespace declaration.
 */
class NamespaceElement extends AbstractElement
{
    /**
     * @var array
     */
    private $uses = [];

    /**
     * @var ClassElement[]
     */
    private $classes = [];

    /**
     * @param ClassElement $class
     * @return $this
     */
    public function addClass(ClassElement $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    /**
     * @return ClassElement[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $uses
     * @return $this
     */
    public function setUses(array $uses)
    {
        $this->uses = $uses;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addUse($class)
    {
        if (array_search($class, $this->uses) === false) {
            $this->uses[] = $class;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * {@inheritdoc}
     *
     * @param ArraySerializer $serializer Class used to render array values for default properties and etc.
     */
    public function render($indentLevel = 0, ArraySerializer $serializer = null)
    {
        $result = [$this->renderComment($indentLevel)];

        if (!empty($this->getName())) {
            $result[] = 'namespace ' . trim($this->getName(), '\\');
            $result[] = "{";
        }

        foreach ($this->uses as $class) {
            $result[] = $this->indent('use ' . $class . ';',
                $indentLevel + !empty($this->getName()) ? 1 : 0);
        }

        foreach ($this->classes as $class) {
            $result[] = $class->render($indentLevel + !empty($this->getName()) ? 1 : 0,
                $serializer);
        }

        if (!empty($this->getName())) {
            $result[] = '}';
        }

        return $this->join($result, $indentLevel);
    }
}