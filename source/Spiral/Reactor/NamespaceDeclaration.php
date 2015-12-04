<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

/**
 * Represent namespace declaration.
 */
class NamespaceDeclaration
{
    /**
     * @var array
     */
    protected $uses = [];

    /**
     * @var ClassDeclaration[]
     */
    protected $classes = [];

    /**
     * @param ClassDeclaration $element
     * @return $this
     */
    public function addClass(ClassDeclaration $element)
    {
        $this->classes[] = $element;

        return $this;
    }

    /**
     * @return ClassDeclaration[]
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
    public function lines($indent = 0, ArraySerializer $serializer = null)
    {
        $lines = $this->commentLines($this->comment);

        if (!empty($this->getName())) {
            $lines[] = 'namespace ' . trim($this->getName(), '\\');
            $lines[] = "{";
        }

        foreach ($this->uses as $class) {
            $lines[] = $this->indent("use {$class};", !empty($this->getName()) ? 1 : 0);
        }

        $result[] = "";

        foreach ($this->classes as $class) {
            $lines = array_merge($lines,
                $class->lines(!empty($this->getName()) ? 1 : 0, $serializer)
            );

            $lines[] = "";
        }

        if ($lines[count($lines) - 1] == "") {
            //We don't need blank lines at the end
            unset($lines[count($lines) - 1]);
        }

        if (!empty($this->getName())) {
            $lines[] = '}';
        }

        return $this->indentLines($lines, $indent);
    }
}