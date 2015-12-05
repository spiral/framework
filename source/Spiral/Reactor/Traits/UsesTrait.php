<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Reactor\Traits;

/**
 * Provide ability to declared namespase uses.
 */
trait UsesTrait
{
    /**
     * @var array
     */
    private $uses = [];

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
     * Declare uses in a form of array [class => alias|null].
     *
     * @param array $uses
     * @return $this
     */
    public function setUses(array $uses)
    {
        $this->uses = [];
        foreach ($uses as $class => $alias) {
            $this->addUse($class, $alias);
        }

        return $this;
    }

    /**
     * @param      $class
     * @param null $alias
     * @return $this
     */
    public function addUse($class, $alias = null)
    {
        $this->uses[ltrim($class, '\\')] = $alias;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function removeUse($class)
    {
        unset($this->uses[ltrim($class, '\\')]);

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
     * @param int $indentLevel
     * @return string
     */
    private function renderUses($indentLevel = 0)
    {
        $lines = [];
        foreach ($this->uses as $class => $alias) {
            $line = "use {$class}";

            if (!empty($alias)) {
                $line .= " as {$alias};";
            } else {
                $line .= ";";
            }

            $lines = $this->indent($line, $indentLevel);
        }

        return join("\n", $lines);
    }

    /**
     * @param string $string
     * @param int    $indent
     * @return string
     */
    abstract protected function indent($string, $indent = 0);
}