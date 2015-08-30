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
    protected $uses = [];

    /**
     * @var ClassElement[]
     */
    protected $classes = [];

    /**
     * @param ClassElement $element
     * @return $this
     */
    public function addClass(ClassElement $element)
    {
        $this->classes[] = $element;

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
     * @param string $class
     * @return bool
     */
    public function hasUse($class)
    {
        $class = ltrim($class, '\\');

        return array_search($class, $this->uses) !== false;
    }

    /**
     * @param array $classes
     * @return $this
     */
    public function setUses(array $classes)
    {
        $this->uses = [];
        foreach ($classes as $use) {
            $this->addUse($use);
        }

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addUse($class)
    {
        $class = ltrim($class, '\\');
        if (array_search($class, $this->uses) === false) {
            $this->uses[] = $class;
        }

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function removeUse($class)
    {
        $class = ltrim($class, '\\');
        if (($index = array_search($class, $this->uses)) !== false) {
            unset($this->uses[$index]);
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
     * @param ArraySerializer $serializer Class used to render array values for default properties
     *                                    and etc.
     */
    public function render($indentLevel = 0, ArraySerializer $serializer = null)
    {
        $result = [$this->renderComment($indentLevel)];

        if (!empty($this->getName())) {
            $result[] = 'namespace ' . trim($this->getName(), '\\');
            $result[] = "{";
        }

        foreach ($this->uses as $class) {
            $result[] = $this->indent(
                'use ' . $class . ';', $indentLevel + !empty($this->getName()) ? 1 : 0
            );
        }

        foreach ($this->classes as $class) {
            $result[] = $class->render(
                $indentLevel + !empty($this->getName()) ? 1 : 0, $serializer
            );
        }

        if (!empty($this->getName())) {
            $result[] = '}';
        }

        return $this->joinLines($result, $indentLevel);
    }
}