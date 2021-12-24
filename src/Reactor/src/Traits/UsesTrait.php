<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

/**
 * Provide ability to declared namespace uses.
 */
trait UsesTrait
{
    /**
     * @var array
     */
    private $uses = [];

    public function uses(string $class): bool
    {
        $class = ltrim($class, '\\');

        return array_key_exists($class, $this->uses);
    }

    /**
     * Declare uses in a form of array [class => alias|null]. Existed uses will be dropped.
     *
     *
     */
    public function setUses(array $uses): self
    {
        $this->uses = [];

        return $this->addUses($uses);
    }

    /**
     * Add additional set of uses.
     *
     *
     */
    public function addUses(array $uses): self
    {
        foreach ($uses as $class => $alias) {
            $this->addUse($class, $alias);
        }

        return $this;
    }

    /**
     * @param string $alias Optional.
     *
     */
    public function addUse(string $class, string $alias = null): self
    {
        $this->uses[ltrim($class, '\\')] = $alias;

        return $this;
    }

    public function removeUse(string $class): self
    {
        unset($this->uses[ltrim($class, '\\')]);

        return $this;
    }

    public function getUses(): array
    {
        return $this->uses;
    }

    abstract protected function addIndent(string $string, int $indent = 0): string;

    private function renderUses(int $indentLevel = 0): string
    {
        $lines = [];
        foreach ($this->getUses() as $class => $alias) {
            $line = "use {$class}";

            if (!empty($alias)) {
                $line .= " as {$alias};";
            } else {
                $line .= ';';
            }

            $lines[] = $this->addIndent($line, $indentLevel);
        }

        return implode("\n", $lines);
    }
}
